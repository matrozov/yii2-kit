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
use yii\httpclient\Exception;
use yii\web\UploadedFile;

/**
 * Реализует доступ к файлу по аттрибуту с указанным названием
 * Привязка:
 * [
 *     'class' => FileBehavior::class,
 *     'attribute' => 'image',
 *     'fileClass' => 'app\models\File',
 * ]
 *
 * При привязке рекомендуется указывать следующий phpdoc для модели:
 * * @var File $image
 * * @method ActiveQuery getImage()
 * * @see FileBehavior
 *
 * Реализует доступ к файлам на чтение и запись через прямой вызов аттрибута модели:
 * $model->image = ...
 * ... = $model->image->id
 *
 * Реализует механику связи сущностей через стандартную механику взаимоотношений включая поддержку ленивой загрузки и
 * предзагрузки:
 *
 * $model->getImage()
 *
 * ModelClass::find()
 *     ->with(['image'])
 *
 *
 * @property ActiveRecord $owner
 */
class FileBehavior extends Behavior
{
    public string $attribute;

    public File|string $fileClass;

    private File|null|false $newFile = false;

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
    protected function isMyMethod(string $method): bool
    {
        return strtolower($method) == strtolower('get' . $this->attribute);
    }

    /**
     * @param string $property
     *
     * @return bool
     */
    protected function isMyProperty(string $property): bool
    {
        return strtolower($property) == strtolower($this->attribute);
    }

    /**
     * @return File|null
     */
    protected function getStoredValue(): File|null
    {
        if ($this->owner->isRelationPopulated($this->attribute)) {
            return $this->owner->{$this->attribute};
        }

        $relation = $this->owner->getRelation($this->attribute);

        /** @var File|null $model */
        $model = $relation->one();

        $this->owner->populateRelation($this->attribute, $model);

        return $model;
    }

    /**
     * @return void
     * @throws ErrorException
     * @throws Exception
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
        $this->getStoredValue()?->delete();
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
     * @throws ErrorException
     * @throws Exception
     * @throws ModelValidationException
     * @throws StaleObjectException
     * @throws Throwable
     */
    protected function save(): void
    {
        if ($this->newFile === false) {
            return;
        }

        $this->getStoredValue()?->delete();

        if ($this->newFile === null) {
            return;
        }

        $this->newFile->target_class     = $this->getOwnerClass();
        $this->newFile->target_id        = $this->owner->getPrimaryKey();
        $this->newFile->target_attribute = $this->attribute;

        if (!$this->newFile->save()) {
            throw new ModelValidationException($this->newFile);
        }

        $this->owner->populateRelation($this->attribute, $this->newFile);

        $this->newFile = false;
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
    public function canGetProperty($name, $checkVars = true): bool
    {
        if (!$this->isMyProperty($name)) {
            return parent::canGetProperty($name, $checkVars);
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
     * @param string                        $name
     * @param UploadedFile|File|string|null $value
     *
     * @return void
     * @throws ErrorException
     * @throws Exception
     * @throws UnknownPropertyException
     */
    public function __set($name, $value): void
    {
        if (!$this->isMyProperty($name)) {
            parent::__set($name, $value);
            return;
        }

        if ($value === null) {
            $this->newFile = null;
        } elseif ($value instanceof UploadedFile) {
            $this->newFile = ($this->fileClass)::createFromUploadedFile($value);
        } elseif ($value instanceof File) {
            $this->newFile = ($this->fileClass)::createFromFile($value, $this->getStoredValue());
        } elseif (is_string($value)) {
            $this->newFile = ($this->fileClass)::createFromUrl($value);
        } else {
            throw new InvalidArgumentException('Invalid value type');
        }
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws UnknownPropertyException
     */
    public function __get($name): mixed
    {
        if (!$this->isMyProperty($name)) {
            return parent::__get($name);
        }

        if ($this->newFile !== false) {
            return $this->newFile;
        }

        return $this->getStoredValue();
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
        $query->multiple     = false;

        $query->andOnCondition([
            'target_class'     => $this->getOwnerClass(),
            'target_attribute' => $this->attribute,
        ]);

        return $query;
    }
}
