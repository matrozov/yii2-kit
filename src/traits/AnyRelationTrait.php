<?php

declare(strict_types=1);

namespace matrozov\yii2kit\traits;

use matrozov\yii2kit\components\AnyRelationQuery;
use yii\db\ActiveRecordInterface;

/**
 * Набор методов hasAnyOne() и hasAnyMany() реализует механизм связывания со сложной механикой выборки. Механизм
 * реализует поддержку привычного "ленивого" связывания реализуемого поверх relation с поддержкой массовой
 * загрузки через with()
 *
 * !!! Обратите внимание, что AnyRelationQuery нельзя использовать как обычный ActiveQuery, так как фактического
 * запроса к базе данных механизм не реализует
 */
trait AnyRelationTrait
{
    /**
     * @param string $parentField
     * @param callable $callback
     * @param bool $multiple
     * @return AnyRelationQuery
     */
    protected function createAnyRelationQuery(string $parentField, callable $callback, bool $multiple): AnyRelationQuery
    {
        /* @var $class ActiveRecordInterface */
        $query = new AnyRelationQuery($this::class);
        $query->primaryModel = $this;
        $query->link         = [$parentField];
        $query->callback     = $callback;
        $query->multiple     = $multiple;
        return $query;
    }

    /**
     * Вы можете использовать hasAnyOne для реализации связи со сложными запросами или каскада запросов возвращая
     * индексный массив с ключами эквивалентными указанному полю родительской модели
     *
     * public function getMyField(): AnyRelationQuery
     * {
     *     return $this->>hasAnyOne('id', function ($primaryModels) {
     *          $primaryIds = ArrayHelper::getColumn($primaryModels, 'id');
     *
     *          return (<some query>)
     *              ->where(['primary_id' => $primaryIds)
     *              ->indexBy('primary_id')
     *              ->one();
     *     });
     * }
     *
     * Вы так же можете использовать в виде результата не только ActiveRecord модели, а любые другие, включая
     * скалярные типы данных
     *
     * public function getMyField(): AnyRelationQuery
     * {
     *     return $this->>hasAnyOne('id', function ($primaryModels) {
     *         $primaryIds = ArrayHelper::getColumn($primaryModels, 'id');
     *
     *         return [<primary_id> => <value>];
     *     });
     * }
     *
     * !!! Обратите внимание, что AnyRelationQuery нельзя использовать как обычный ActiveQuery, так как фактического
     * запроса к базе данных механизм не реализует
     *
     * @param string $parentField
     * @param callable $callback
     * @return AnyRelationQuery
     */
    public function hasAnyOne(string $parentField, callable $callback): AnyRelationQuery
    {
        return $this->createAnyRelationQuery($parentField, $callback, false);
    }

    /**
     * Вы можете использовать hasAnyOne для реализации связи со сложными запросами или каскада запросов возвращая
     * индексный массив с ключами эквивалентными указанному полю родительской модели
     *
     * public function getMyField(): AnyRelationQuery
     * {
     *     return $this->>hasAnyOne('id', function ($primaryModels) {
     *         $primaryIds = ArrayHelper::getColumn($primaryModels, 'id');
     *
     *         return (<some query>)
     *             ->where(['primary_id' => $primaryIds)
     *             ->indexBy('primary_id')
     *             ->all();
     *     });
     * }
     *
     * Вы так же можете использовать в виде результата не только ActiveRecord модели, а любые другие, включая
     * скалярные типы данных
     *
     * public function getMyField(): AnyRelationQuery
     * {
     *     return $this->>hasAnyOne('id', function ($primaryModels) {
     *         $primaryIds = ArrayHelper::getColumn($primaryModels, 'id');
     *
     *         return [
     *             <primary_id> => <value>,
     *             <primary_id> => <value>,
     *         ];
     *     });
     * }
     *
     * !!! Обратите внимание, что AnyRelationQuery нельзя использовать как обычный ActiveQuery, так как фактического
     * запроса к базе данных механизм не реализует
     * @param string $parentField
     * @param callable $callback
     * @return AnyRelationQuery
     */
    public function hasAnyMany(string $parentField, callable $callback): AnyRelationQuery
    {
        return $this->createAnyRelationQuery($parentField, $callback, true);
    }
}
