<?php

declare(strict_types=1);

namespace matrozov\yii2common\components\storages;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use matrozov\yii2common\components\Storage;
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

        $this->fs = new Filesystem(new Local(Yii::getAlias($this->path)));
    }
}
