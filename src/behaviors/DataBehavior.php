<?php

declare(strict_types=1);

namespace matrozov\yii2common\behaviors;

use Exception;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;

/**
 * Поведение реализует хранение вложенных свойств в data-аттрибуте.
 * Поддерживает возможность указать значение аттрибута по умолчанию, если он не определён в data-аттрибуте.
 */
class DataBehavior extends Behavior
{
    public string $targetAttribute = 'data';

    /** @var string[] */
    public array $attributes = [];

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
    }

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true): bool
    {
        if (array_key_exists($name, $this->attributes)) {
            return true;
        }

        return parent::canGetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true): bool
    {
        if (array_key_exists($name, $this->attributes)) {
            return true;
        }

        return parent::canSetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function __get($name): mixed
    {
        if (array_key_exists($name, $this->attributes)) {
            return ArrayHelper::getValue($this->owner[$this->targetAttribute], $name, $this->attributes[$name]);
        }

        return parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value): void
    {
        if (array_key_exists($name, $this->attributes)) {
            $data = $this->owner[$this->targetAttribute];

            $data[$name] = $value;

            $this->owner[$this->targetAttribute] = $data;
        } else {
            parent::__set($name, $value);
        }
    }
}
