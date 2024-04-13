<?php

declare(strict_types=1);

namespace matrozov\yii2common\behaviors;

use yii\base\Behavior;
use yii\helpers\ArrayHelper;

/**
 * @property string   $targetAttribute
 * @property string[] $attributes
 */
class DataBehavior extends Behavior
{
    public string $targetAttribute = 'data';

    /** @var string[] */
    public array $attributes = [];

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true): bool
    {
        if (in_array($name, $this->attributes)) {
            return true;
        }

        return parent::canGetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true): bool
    {
        if (in_array($name, $this->attributes)) {
            return true;
        }

        return parent::canSetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function __get($name): mixed
    {
        if (in_array($name, $this->attributes)) {
            return ArrayHelper::getValue($this->owner[$this->targetAttribute], $name);
        }

        return parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value): void
    {
        if (in_array($name, $this->attributes)) {
            $data = $this->owner[$this->targetAttribute];

            $data[$name] = $value;

            $this->owner[$this->targetAttribute] = $data;
        } else {
            parent::__set($name, $value);
        }
    }
}
