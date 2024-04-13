<?php

declare(strict_types=1);

namespace app\components;

use League\Flysystem\DirectoryListing;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Throwable;
use yii\base\Component;

/**
 * @property array $config
 */
abstract class Storage extends Component
{
    /** @var Filesystem */
    protected Filesystem $fs;

    /**
     * @param string $location
     * @param string $content
     * @param bool $overwrite
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
     * @return string
     * @throws FilesystemException
     */
    public function read(string $location): string
    {
        return $this->fs->read($location);
    }

    /**
     * @param string $location
     * @return bool
     * @throws FilesystemException
     */
    public function exists(string $location): bool
    {
        return $this->fs->fileExists($location);
    }

    /**
     * @param string $location
     */
    public function delete(string $location): void
    {
        try {
            $this->fs->delete($location);
        } catch (Throwable $e) {}
    }

    /**
     * @param string $location
     * @param bool|null $deep
     * @return DirectoryListing
     * @throws FilesystemException
     */
    public function listContents(string $location, bool $deep = false): DirectoryListing
    {
        return $this->fs->listContents($location, $deep);
    }
}
