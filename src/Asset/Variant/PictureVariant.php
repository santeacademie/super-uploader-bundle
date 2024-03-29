<?php

namespace Santeacademie\SuperUploaderBundle\Asset\Variant;


class PictureVariant extends AbstractVariant
{
    public function __construct(
        string $variantTypeClass,
        bool $required,
        string $name,
        string $label,
        private int $width,
        private int $height,
        ?string $extension = ''
    )
    {
        parent::__construct($variantTypeClass, $required, $name, $label, $extension);
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


}