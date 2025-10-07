<?php

declare(strict_types=1);

namespace matrozov\yii2kit\components;

use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecordInterface;
use yii\db\Command;

class AnyRelationQuery extends ActiveQuery
{
    public mixed $callback;

    /**
     * @param string $name
     * @param ActiveRecordInterface[] $primaryModels
     * @return array
     * @throws InvalidConfigException
     */
    public function populateRelation($name, &$primaryModels): array
    {
        $primaryModelField = reset($this->link);

        $childModels = call_user_func($this->callback, $primaryModels);

        foreach ($primaryModels as $primaryModel) {
            $key = $primaryModel[$primaryModelField];

            if (array_key_exists($key, $childModels)) {
                $value = $childModels[$key];
            } else {
                $value = $this->multiple ? [] : null;
            }

            $primaryModel->populateRelation($name, $value);
        }

        return $childModels;
    }

    /**
     * @param $name
     * @param $model
     * @return array|mixed|null
     */
    public function findFor($name, $model): mixed
    {
        $primaryModelField = reset($this->link);

        $childModels = call_user_func($this->callback, [$model]);

        $key = $model[$primaryModelField];

        if (array_key_exists($key, $childModels)) {
            return $childModels[$key];
        }

        return $this->multiple ? [] : null;
    }

    /**
     * @param $db
     * @return Command
     */
    public function createCommand($db = null): Command
    {
        throw new InvalidCallException('Can\'t use AnyRelationQuery as ActiveQuery');
    }
}
