<?php

declare(strict_types=1);

namespace matrozov\yii2kit\behaviors;

use thamtech\uuid\helpers\UuidHelper;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;

/**
 * UuidBehavior
 */
class UuidBehavior extends Behavior
{
    public array $attributes = ['id'];

    /**
     * @return array
     */
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_INIT => 'generate',
        ];
    }

    /**
     * @return void
     */
    public function generate(): void
    {
        foreach ($this->attributes as $attribute) {
            $this->owner->$attribute = UuidHelper::uuid();
        }
    }
}
