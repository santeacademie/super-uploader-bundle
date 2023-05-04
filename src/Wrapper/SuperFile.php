<?php

namespace Santeacademie\SuperUploaderBundle\Wrapper;

use League\Flysystem\FilesystemOperator;
use Santeacademie\SuperUploaderBundle\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

class SuperFile extends File
{

    public function __construct(
        string $path,
        bool $checkPath,
        private FilesystemOperator $filesystemOperator)
    {
        if ($checkPath && !$this->filesystemOperator->fileExists($path)) {
            throw new FileNotFoundException($path);
        }
        parent::__construct($path, false);
    }

    public function getContent(): string
    {
        return $this->filesystemOperator->read($this->getPathname());
    }
}
