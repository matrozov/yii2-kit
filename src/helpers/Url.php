<?php

declare(strict_types=1);

namespace matrozov\yii2common\helpers;

use Exception;
use yii\helpers\ArrayHelper;

class Url extends \yii\helpers\Url
{
    /**
     * @param string      $dataUri
     * @param string|null $type
     * @param array|null  $attributes
     * @param string|null $data
     *
     * @return bool
     * @throws Exception
     */
    public static function parseDataUri(string $dataUri, string|null &$type = null, array|null &$attributes = null, string|null &$data = null): bool
    {
        if (!preg_match('#^data:(?<type>[^;,]+)?(?<attributes>;[^,]+)*(?<data>.*)$#i', $dataUri, $matches)) {
            return false;
        }

        $type       = $matches['type'] ?: null;
        $attributes = $matches['attributes'];
        $data       = $matches['data'];

        if (!empty($attributes)) {
            if (!preg_match_all('#;(?<name>[^;=]+)(=(?<value>[^;]+))?#i', $matches['attributes'] . ';', $matches)) {
                return false;
            }

            $attributes = array_combine($matches['name'], $matches['value']);

            array_walk($attributes, function ($value, $key) use (&$attributes) {
                $attributes[$key] = empty($value) ? true : $value;
            });
        } else {
            $attributes = [];
        }

        if (ArrayHelper::getValue($attributes, 'base64')) {
            $data = base64_decode($data);
        }

        return true;
    }
}
