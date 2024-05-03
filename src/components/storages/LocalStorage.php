<?php

declare(strict_types=1);

namespace matrozov\yii2kit\components\storages;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use matrozov\yii2kit\components\Storage;
use Yii;

class LocalStorage extends Storage
{
    public string $path = '@app/storage';

    /**
     * @return void
     */
    public function init(): void
    {
        parent::init();

        $this->fs = new Filesystem(new LocalFilesystemAdapter(Yii::getAlias($this->path)));
    }
}
