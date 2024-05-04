<?php

declare(strict_types=1);

namespace matrozov\yii2kit\behaviors;

use matrozov\yii2kit\exceptions\ModelValidationException;
use matrozov\yii2kit\interfaces\FileTargetClassInterface;
use matrozov\yii2kit\models\File;
use Throwable;
use yii\base\Behavior;
use yii\base\ErrorException;
use yii\base\InvalidArgumentException;
use yii\base\UnknownPropertyException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Exception;
use yii\web\UploadedFile;

/**
 * Реализует доступ к файлам по аттрибуту с указанным названием
 * Привязка:
 * [
 *     'class' => FilesBehavior::class,
 *     'attribute' => 'images',
 *     'fileClass' => 'app\models\File',
 * ]
 *
 * При привязке рекомендуется указывать следующий phpdoc для модели:
 * * @var File[] $images
 * * @method ActiveQuery getImages()
 * * @see FilesBehavior
 *
 * Реализует доступ к файлам на чтение и запись через прямой вызов аттрибута модели как ко множеству файлов, так и к
 * отдельным файлам по ключу:
 * $model->image = ...
 * $model->image_<sub-key> = ...
 * ... = $model->image->id
 * ... = $model->image_<sub-key>->id
 *
 * Реализует механику связи сущностей через стандартную механику взаимоотношений включая поддержку ленивой загрузки и
 * предзагрузки:
 *
 * $model->getImages()
 *
 * ModelClass::find()
 *     ->with(['images'])
 *
 *
 * @property ActiveRecord $owner
 */
class FilesBehavior extends Behavior
{
    public string $attribute;

    public File|string $fileClass;

    /**
     * @var File[]|false
     */
    private array|false $_oldFiles = false;

    /**
     * @var File[]|false
     */
    private array|false $_newFiles = false;

    /**
     * @return array[]
     */
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_AFTER_INSERT => [$this, 'onAfterSave'],
            BaseActiveRecord::EVENT_AFTER_UPDATE => [$this, 'onAfterSave'],
            BaseActiveRecord::EVENT_AFTER_DELETE => [$this, 'onAfterDelete'],
        ];
    }

    /**
     * @param string $method
     *
     * @return bool
     */
    public function isMyMethod(string $method): bool
    {
        return strtolower($method) == strtolower('get' . $this->attribute);
    }

    /**
     * @param string      $property
     * @param string|null $key
     *
     * @return bool
     */
    public function isMyProperty(string $property, string|null &$key = null): bool
    {
        $key = null;

        if (strtolower($property) === strtolower($this->attribute)) {
            return true;
        }

        if (!preg_match('#^' . preg_quote($this->attribute) . '_(.*)$#i', $property, $matches)) {
            return false;
        }

        $key = $matches[1];

        return true;
    }

    /**
     * @return File[]
     */
    protected function getStoredValue(): array
    {
        if ($this->_oldFiles !== false) {
            return $this->_oldFiles;
        }

        if ($this->owner->isRelationPopulated($this->attribute)) {
            $this->_oldFiles = $this->owner->{$this->attribute};

            return $this->_oldFiles;
        }

        $relation = $this->owner->getRelation($this->attribute);

        /** @var File[] $models */
        $models = $relation
            ->indexBy('key')
            ->all();

        $this->owner->populateRelation($this->attribute, $models);

        $this->_oldFiles = $models;

        return $models;
    }

    /**
     * @return void
     * @throws ModelValidationException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function onAfterSave(): void
    {
        $this->save();
    }

    /**
     * @return void
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function onAfterDelete(): void
    {
        $files = $this->getStoredValue();

        foreach ($files as $file) {
            $file->delete();
        }
    }

    /**
     * @return string
     */
    protected function getOwnerClass(): string
    {
        return ($this->owner instanceof FileTargetClassInterface) ? $this->owner->fileTargetClass() : get_class($this->owner);
    }

    /**
     * @return void
     * @throws ModelValidationException
     * @throws StaleObjectException
     * @throws Throwable
     */
    protected function save(): void
    {
        if ($this->_newFiles === false) {
            return;
        }

        $files = $this->getStoredValue();

        foreach ($files as $key => $file) {
            if (!array_key_exists($key, $this->_newFiles)) {
                $file->delete();
            }
        }

        foreach ($this->_newFiles as $newFile) {
            $newFile->target_class     = $this->getOwnerClass();
            $newFile->target_id        = $this->owner->getPrimaryKey();
            $newFile->target_attribute = $this->attribute;

            if (!$newFile->save()) {
                throw new ModelValidationException($newFile);
            }
        }

        $this->owner->populateRelation($this->attribute, $this->_newFiles);

        $this->_oldFiles = $this->_newFiles;
        $this->_newFiles = false;
    }

    /**
     * @param string $name
     * @param bool   $checkVars
     *
     * @return bool
     */
    public function hasProperty($name, $checkVars = true): bool
    {
        if (!$this->isMyProperty($name)) {
            return parent::hasProperty($name, $checkVars);
        }

        return true;
    }

    /**
     * @param string $name
     * @param bool   $checkVars
     *
     * @return bool
     */
    public function canSetProperty($name, $checkVars = true): bool
    {
        if (!$this->isMyProperty($name)) {
            return parent::canSetProperty($name, $checkVars);
        }

        return true;
    }

    /**
     * @param string $name
     * @param bool   $checkVars
     *
     * @return bool
     */
    public function canGetProperty($name, $checkVars = true): bool
    {
        if (!$this->isMyProperty($name)) {
            return parent::canGetProperty($name, $checkVars);
        }

        return true;
    }

    /**
     * @param string $name
     * @param UploadedFile[]|File[]|string[]|null[]|UploadedFile|File|string|null $value
     *
     * @return void
     * @throws ErrorException
     * @throws Exception
     * @throws UnknownPropertyException
     * @throws \Exception
     */
    public function __set($name, $value): void
    {
        if (!$this->isMyProperty($name, $key)) {
            parent::__set($name, $value);
            return;
        }

        if ($key === null) {
            // Замена всех файлов

            if ($value === null) {
                $this->_newFiles = [];
            } elseif (is_array($value)) {
                $this->_newFiles = [];

                foreach ($value as $key => $newFile) {
                    $this->__set($this->attribute . '_' . $key, $newFile);
                }
            } else {
                throw new ErrorException('Invalid value type');
            }
        } else {
            // Замена конкретного значения

            $newFiles = $this->_newFiles;

            if ($newFiles === false) {
                $newFiles = $this->getStoredValue();
            }

            if ($value === null) {
                unset($newFiles[$key]);
            } else {
                if ($value instanceof UploadedFile) {
                    $newFiles[$key] = ($this->fileClass)::createFromUploadedFile($value);
                } elseif ($value instanceof File) {
                    $newFiles[$key] = ($this->fileClass)::createFromFile($value, ArrayHelper::getValue($this->getStoredValue(), $key));
                } elseif (is_string($value)) {
                    $newFiles[$key] = ($this->fileClass)::createFromUrl($value);
                } else {
                    throw new InvalidArgumentException('Invalid value type');
                }

                $newFiles[$key]->key = $key;
            }

            $this->_newFiles = $newFiles;
        }

        // Удаляем ранее сохранённые данные в основной модели
        unset($this->owner->{$this->attribute});
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws UnknownPropertyException
     * @throws \Exception
     */
    public function __get($name): mixed
    {
        if (!$this->isMyProperty($name, $key)) {
            return parent::__get($name);
        }

        if ($key === null) {
            if ($this->_newFiles !== false) {
                return $this->_newFiles;
            }

            return $this->getStoredValue();
        }

        if ($this->_newFiles !== false) {
            return ArrayHelper::getValue($this->_newFiles, $key);
        }

        return ArrayHelper::getValue($this->getStoredValue(), $key);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasMethod($name): bool
    {
        if (!$this->isMyMethod($name)) {
            return parent::hasMethod($name);
        }

        return true;
    }

    /**
     * @param string $name
     * @param array  $params
     *
     * @return ActiveQuery|mixed
     */
    public function __call($name, $params): mixed
    {
        if (!$this->isMyMethod($name)) {
            return parent::__call($name, $params);
        }

        $query = ($this->fileClass)::find();

        $query->primaryModel = $this->owner;
        $query->link         = ['target_id' => $this->owner::primaryKey()[0]];
        $query->multiple     = true;

        $query->andOnCondition([
            'target_class'     => $this->getOwnerClass(),
            'target_attribute' => $this->attribute,
        ]);

        return $query;
    }
}
