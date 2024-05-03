<?php

declare(strict_types=1);

namespace matrozov\yii2kit\traits;

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
