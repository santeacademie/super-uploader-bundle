<?php

namespace Santeacademie\SuperUploaderBundle\Asset\Variant;


class VideoVariant extends AbstractVariant
{

    public function __construct(
        string $variantTypeClass,
        bool $required,
        string $name,
        string $label,
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

    public function getExtension(): string
    {
        return $this->extension;
    }
}