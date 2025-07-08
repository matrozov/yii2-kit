<?php

declare(strict_types=1);

namespace matrozov\yii2kit\behaviors;

use Imagick;
use ImagickException;
use matrozov\yii2kit\helpers\FileHelper;
use matrozov\yii2kit\models\Image;
use yii\base\ErrorException;
use yii\base\UnknownPropertyException;
use yii\httpclient\Exception;

class ImageBehavior extends FileBehavior
{
    public const CROP_METHOD_FIT_TO_SIZE = 'fit_to_size';

    public int|null $targetWidth  = null;
    public int|null $targetHeight = null;
    public string   $targetFormat = Image::FORMAT_JPEG;

    public string   $cropMethod   = self::CROP_METHOD_FIT_TO_SIZE;

    /**
     * @param $name
     * @param $value
     * @return void
     * @throws ImagickException
     * @throws ErrorException
     * @throws UnknownPropertyException
     * @throws Exception
     */
    public function __set($name, $value): void
    {
        if (!$this->isMyProperty($name)) {
            parent::__set($name, $value);
            return;
        }

        $value = FileHelper::valueToFile($this->fileClass, $value, $this->getStoredValue());

        if ($value === null) {
            parent::__set($name, $value);
            return;
        }

        $image = new Imagick();
        $image->readImageBlob($value->content);

        if (($this->targetWidth !== null) && ($this->targetHeight !== null)) {
            switch ($this->cropMethod) {
                case self::CROP_METHOD_FIT_TO_SIZE: {
                    $image->thumbnailImage($this->targetWidth, $this->targetHeight, true);
                } break;
            }
        }

        if ($this->targetFormat !== null) {
            $image->setImageFormat($this->targetFormat);
        }

        $newFile = new Image();

        $newFile->name      = $value->name;
        $newFile->size      = $value->size;
        $newFile->mime_type = $image->getImageMimeType();
        $newFile->width     = $image->getImageWidth();
        $newFile->height    = $image->getImageHeight();
        $newFile->content   = $image->getImageBlob();

        parent::__set($name, $newFile);
    }
}
