<?php

declare(strict_types=1);

namespace matrozov\yii2kit\components;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use yii\base\Component;

abstract class Storage extends Component
{
    /** @var Filesystem */
    protected Filesystem $fs;

    /**
     * @param string $location
     * @param string $content
     * @param bool $overwrite
     *
     * @return void
     * @throws FilesystemException
     */
    public function write(string $location, string $content, bool $overwrite = false): void
    {
        if (!$overwrite && $this->exists($location)) {
            return;
        }

        $this->fs->write($location, $content);
    }

    /**
     * @param string $location
     *
     * @return string
     * @throws FilesystemException
     */
    public function read(string $location): string
    {
        return $this->fs->read($location);
    }

    /**
     * @param string $location
     *
     * @return bool
     * @throws FilesystemException
     */
    public function exists(string $location): bool
    {
        return $this->fs->has($location);
    }

    /**
     * @param string $location
     * @throws FilesystemException
     */
    public function delete(string $location): void
    {
        $this->fs->delete($location);
    }

    /**
     * @param string $location
     * @param bool|null $deep
     *
     * @return array
     * @throws FilesystemException
     */
    public function listContents(string $location, bool $deep = false): array
    {
        return $this->fs->listContents($location, $deep)->toArray();
    }
}
