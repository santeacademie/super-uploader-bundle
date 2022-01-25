<?php

namespace Santeacademie\SuperUploaderBundle\Asset\Variant;

class PdfVariant extends AbstractVariant
{
    public const EXTENSION = 'pdf';

    /**
     * If you have Ghostscript installed in a non-standard-location that can not
     * be found via the 'which gs' command, you have to set the path with $ghostscriptPath
     *
     * @param string $variantTypeClass
     * @param bool $required
     * @param string $name
     * @param string $label
     * @param string|null $extension
     * @param int|null $sizeLimit
     * @param string|null $ghostscriptPath
     */
    public function __construct(
        string $variantTypeClass,
        bool $required,
        string $name,
        string $label,
        ?string $extension = '',
        private ?int $sizeLimit = null,
        private ?string $ghostscriptPath = null
    ) {
        parent::__construct($variantTypeClass, $required, $name, $label, $extension);
    }

    public function getSizeLimit(): int
    {
        return $this->sizeLimit;
    }

    public function getGhostscriptPath(): string
    {
        return $this->ghostscriptPath;
    }
}