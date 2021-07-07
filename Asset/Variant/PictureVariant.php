<?php

namespace Santeacademie\SuperUploaderBundle\Asset\Variant;

use Santeacademie\SuperUploaderBundle\Form\AbstractVariantType;
use Santeacademie\SuperUploaderBundle\Asset\Variant\Interface\StaticExtensionVariantInterface;

class PictureVariant extends AbstractVariant implements StaticExtensionVariantInterface
{
    public function __construct(
        string $variantTypeClass,
        bool $required,
        string $name,
        string $label,
        private int $width,
        private int $height,
        private string $extension
    )
    {
        parent::__construct($variantTypeClass, $required, $name, $label);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }



}