<?php

declare(strict_types=1);

namespace matrozov\yii2common\components;

use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use yii\base\Component;

abstract class Storage extends Component
{
    /** @var Filesystem */
    protected Filesystem $fs;

    /**
     * @param string $location
     * @param string $content
     * @param bool   $overwrite
     *
     * @return void
     * @throws FileExistsException
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
     * @throws FileNotFoundException
     */
    public function read(string $location): string
    {
        return $this->fs->read($location);
    }

    /**
     * @param string $location
     *
     * @return bool
     */
    public function exists(string $location): bool
    {
        return $this->fs->has($location);
    }

    /**
     * @param string $location
     *
     * @throws FileNotFoundException
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
     */
    public function listContents(string $location, bool $deep = false): array
    {
        return $this->fs->listContents($location, $deep);
    }
}
