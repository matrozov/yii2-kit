<?php

declare(strict_types=1);

namespace matrozov\yii2kit\models;

use matrozov\yii2kit\behaviors\DataBehavior;

/**
 * @property string $format
 * @property int    $width
 * @property int    $height
 */
class Image extends File
{
    public const FORMAT_JPEG = 'jpg';
    public const FORMAT_PNG  = 'png';

    protected const FORMAT_EXTENSION = [
        self::FORMAT_JPEG => 'jpg',
        self::FORMAT_PNG  => 'png',
    ];

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['data'] = [
            'class' => DataBehavior::class,
            'attributes' => [
                'width'  => 0,
                'height' => 0,
            ],
        ];

        return $behaviors;
    }
}
