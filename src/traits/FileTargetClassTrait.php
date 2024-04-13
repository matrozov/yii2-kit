<?php

declare(strict_types=1);

namespace matrozov\yii2common\traits;

trait FileTargetClassTtait
{
    /**
     * @return string
     */
    public function fileTargetClass(): string
    {
        return static::class;
    }
}
