<?php

namespace Santeacademie\SuperUploaderBundle\Asset\Variant;

class DocumentVariant extends AbstractVariant
{

    public function __construct(
        string $variantTypeClass,
        bool $required,
        string $name,
        string $label,
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

}