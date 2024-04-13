<?php

declare(strict_types=1);

namespace matrozov\yii2common\helpers;

use Yii;
use yii\base\InvalidConfigException;

class FileHelper extends \yii\helpers\FileHelper
{
    /**
     * @param string      $file
     * @param string      $content
     * @param string|null $magicFile
     * @param bool        $checkExtension
     *
     * @return string|null
     * @throws InvalidConfigException
     */
    public static function getMimeTypeByContent(string $file, string $content, string|null $magicFile = null, bool $checkExtension = true): string|null
    {
        if ($magicFile !== null) {
            $magicFile = Yii::getAlias($magicFile);
        }

        if (!extension_loaded('fileinfo')) {
            if ($checkExtension) {
                return static::getMimeTypeByExtension($file, $magicFile);
            }

            throw new InvalidConfigException('The fileinfo PHP extension is not installed.');
        }

        $info = finfo_open(FILEINFO_MIME_TYPE, $magicFile);

        if ($info) {
            $result = finfo_buffer($info, $content);
            finfo_close($info);

            if ($result !== false) {
                return $result;
            }
        }

        return $checkExtension ? static::getMimeTypeByExtension($file, $magicFile) : null;
    }
}
