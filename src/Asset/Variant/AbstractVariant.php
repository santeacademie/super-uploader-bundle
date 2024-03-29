<?php

namespace Santeacademie\SuperUploaderBundle\Asset\Variant;

use League\Flysystem\FileAttributes;
use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Wrapper\FallbackResourceFile;
use Santeacademie\SuperUploaderBundle\Wrapper\TemporaryFile;
use Santeacademie\SuperUploaderBundle\Wrapper\SuperFile;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

abstract class AbstractVariant
{
    const DEFAULT_FALLBACK_RESOURCE = true;

    protected $asset;
    protected $temporaryFile;
    protected $variantFile;

    function __construct(
        protected string $variantTypeClass,
        protected bool $required,
        protected string $name,
        protected string $label,
        protected string $extension = ''
    )
    {
        if (!class_exists($variantTypeClass)) {
            throw new InvalidOptionsException(sprintf('VariantType class "%s" doesn\'t exist.', $variantTypeClass));
        }

        $this->extension = empty($this->extension) ? '' : $this->extension;
    }

    public function getVariantTypeClass(): string
    {
        return $this->variantTypeClass;
    }

    public function getRequired(): bool
    {
        return $this->required;
    }

    public function isRequired(): bool
    {
        return $this->getRequired();
    }

    public function setRequired(?bool $required = false): self
    {
        $this->required = $required;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getAsset(): AbstractAsset
    {
        return $this->asset;
    }

    public function setAsset(AbstractAsset $asset): self
    {
        $this->asset = $asset;

        return $this;
    }

    public function getTemporaryFile(): ?TemporaryFile
    {
        return $this->temporaryFile;
    }

    public function setTemporaryFile(TemporaryFile $temporaryFile): self
    {
        $this->temporaryFile = $temporaryFile;

        return $this;
    }

    public function getVariantFile(bool $fallbackResource = AbstractVariant::DEFAULT_FALLBACK_RESOURCE): SuperFile|null
    {
        if (!$fallbackResource && $this->variantFile instanceof FallbackResourceFile) {
            return null;
        }

        return $this->variantFile;
    }

    public function setVariantFile($variantFile): self
    {
        $this->variantFile = $variantFile;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }



}