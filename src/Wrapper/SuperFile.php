<?php

namespace Santeacademie\SuperUploaderBundle\Wrapper;

use League\Flysystem\FilesystemOperator;
use Santeacademie\SuperUploaderBundle\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\MimeTypes;

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

    public function publicUrl(): string
    {
        return $this->filesystemOperator->publicUrl($this->getPathname());
    }

    public function getMimeType(): ?string
    {
        return $this->filesystemOperator->mimeType($this->getPathname());
    }

    public function guessExtension(): ?string
    {
        $mimeTypes = new MimeTypes();
        $exts = $mimeTypes->getExtensions($this->filesystemOperator->mimeType($this->getPathname()));
        return $exts[0];
    }

    public function getSize(): int
    {
        return $this->filesystemOperator->fileSize($this->getPathname());
    }

    public function __toString(): string
    {
        return $this->publicUrl();
    }
}
