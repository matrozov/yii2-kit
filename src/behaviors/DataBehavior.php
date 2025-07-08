<?php

declare(strict_types=1);

namespace matrozov\yii2kit\behaviors;

use Exception;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;

/**
 * Поведение реализует хранение вложенных свойств в data-аттрибуте.
 * Поддерживает возможность указать значение аттрибута по умолчанию, если он не определён в data-аттрибуте.
 *
 * @property string      $targetAttribute - Название целевого аттрибута в модели
 * @property string[]    $attributes      - Список вложенных аттрибутов (указанные поля будут доступны и как поля модели по прямому обращению $model->"<prefix><attribute>")
 * @property string|null $prefix          - Префикс названия полей в геттере и сеттере
 */
class DataBehavior extends Behavior
{
    public string $targetAttribute = 'data';

    /** @var string[] */
    public array $attributes = [];

    /** @var string|null Getter/Setter field prefix (null - auto from targetAttribute + '_') */
    public string|null $prefix = null;

    public function init(): void
    {
        parent::init();

        $attributes = [];

        foreach ($this->attributes as $attribute => $default) {
            if (is_int($attribute)) {
                $attributes[$default] = null;
            } else {
                $attributes[$attribute] = $default;
            }
        }

        $this->attributes = $attributes;

        if ($this->prefix === null) {
            $this->prefix = $this->targetAttribute . '_';
        }
    }

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true): bool
    {
        if (str_starts_with($this->prefix, $name)) {
            $fieldName = substr($name, strlen($this->prefix));

            if (array_key_exists($fieldName, $this->attributes)) {
                return true;
            }
        }

        return parent::canGetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true): bool
    {
        if (str_starts_with($this->prefix, $name)) {
            $fieldName = substr($name, strlen($this->prefix));

            if (array_key_exists($fieldName, $this->attributes)) {
                return true;
            }
        }

        return parent::canSetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function __get($name): mixed
    {
        if (str_starts_with($this->prefix, $name)) {
            $fieldName = substr($name, strlen($this->prefix));

            if (array_key_exists($fieldName, $this->attributes)) {
                return ArrayHelper::getValue($this->owner[$this->targetAttribute], $fieldName, $this->attributes[$fieldName]);
            }
        }

        return parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value): void
    {
        if (str_starts_with($this->prefix, $name)) {
            $fieldName = substr($name, strlen($this->prefix));

            if (array_key_exists($fieldName, $this->attributes)) {
                $data = $this->owner[$this->targetAttribute];

                $data[$fieldName] = $value;

                $this->owner[$this->targetAttribute] = $data;

                return;
            }
        }

        parent::__set($name, $value);
    }
}
