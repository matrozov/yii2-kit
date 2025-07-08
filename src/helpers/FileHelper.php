<?php

declare(strict_types=1);

namespace matrozov\yii2kit\helpers;

use matrozov\yii2kit\models\File;
use Yii;
use yii\base\ErrorException;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;
use yii\web\UploadedFile;

class FileHelper extends \yii\helpers\FileHelper
{
    /**
     * @throws Exception
     * @throws ErrorException
     */
    public static function valueToFile(string|File $fileClass, mixed $value, mixed $currentValue): File|null
    {
        if ($value === null) {
            return null;
        } elseif ($value instanceof UploadedFile) {
            return $fileClass::createFromUploadedFile($value);
        } elseif ($value instanceof File) {
            return $fileClass::createFromFile($value, $currentValue);
        } elseif (is_string($value)) {
            return $fileClass::createFromUrl($value);
        }

        throw new InvalidArgumentException('Invalid value type');
    }

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
