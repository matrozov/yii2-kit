<?php

declare(strict_types=1);

namespace matrozov\yii2common\traits;

trait FileTargetClassTrait
{
    /**
     * @return string
     */
    public function fileTargetClass(): string
    {
        return self::class;
    }
}
