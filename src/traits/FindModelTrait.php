<?php

namespace app\traits;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;

/**
 * Trait FindModelTrait
 * @package app\traits
 */
trait FindModelTrait
{
    /**
     * @param array $condition
     * @param bool  $throwException
     *
     * @return ActiveRecord|self|null
     * @throws NotFoundHttpException
     */
    public static function findModel(array $condition, bool $throwException = true)
    {
        /** @var ActiveRecord|string $class */
        $class = get_called_class();

        $model = $class::find()->andWhere($condition)->one();

        if (!$model) {
            if ($throwException) {
                throw new NotFoundHttpException('FindModel exception on "' . $class . '": ' . VarDumper::export($condition));
            }

            return null;
        }

        return $model;
    }
}
