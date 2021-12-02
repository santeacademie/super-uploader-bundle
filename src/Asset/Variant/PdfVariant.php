<?php

namespace Santeacademie\SuperUploaderBundle\Asset\Variant;

class PdfVariant extends AbstractVariant
{
    public function __construct(
        string $variantTypeClass,
        bool $required,
        string $name,
        string $label,
        ?string $extension = '',
        private ?int $sizeLimit = null
    ) {
        parent::__construct($variantTypeClass, $required, $name, $label, $extension);
    }

    public function getSizeLimit(): int
    {
        return $this->sizeLimit;
    }
}