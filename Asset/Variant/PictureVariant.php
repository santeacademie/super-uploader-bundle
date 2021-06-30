<?php

namespace Santeacademie\SuperUploaderBundle\Asset\Variant;

use Santeacademie\SuperUploaderBundle\Form\AbstractVariantType;

class PictureVariant extends AbstractVariant
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