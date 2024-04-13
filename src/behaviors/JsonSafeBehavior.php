<?php

declare(strict_types=1);

namespace matrozov\yii2common\behaviors;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Schema;

class JsonSafeBehavior extends Behavior
{
    /**
     * {@inheritDoc}
     */
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'makeSafe',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'makeSafe',
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    public function makeSafe(): void
    {
        /** @var ActiveRecord $activeRecord */
        $activeRecord = $this->owner;

        foreach ($activeRecord::getTableSchema()->columns as $column) {
            if ($column->type != Schema::TYPE_JSON) {
                continue;
            }

            $this->owner[$column->name] = self::makeSafeJson($this->owner[$column->name]);
        }
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    protected static function makeSafeJson(mixed $data): mixed
    {
        if (is_array($data)) {
            $result = [];

            foreach ($data as $key => $value) {
                $result[self::makeSafeJson($key)] = self::makeSafeJson($value);
            }

            return $result;
        } elseif (is_string($data)) {
            return mb_convert_encoding($data, 'utf-8');
        } else {
            return $data;
        }
    }
}
