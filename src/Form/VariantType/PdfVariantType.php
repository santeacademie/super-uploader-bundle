<?php

namespace Santeacademie\SuperUploaderBundle\Form\VariantType;

use Santeacademie\SuperUploaderBundle\Asset\Variant\PdfVariant;
use Santeacademie\SuperUploaderBundle\Form\AbstractVariantType;
use Santeacademie\SuperUploaderBundle\Transformer\PdfTransformer;
use Santeacademie\SuperUploaderBundle\Interface\VariantTansformerInterface;

class PdfVariantType extends AbstractVariantType
{
    public function __construct(
        private PdfTransformer $transformer
    ) {
    }

    public function getTransformer(): VariantTansformerInterface
    {
        return $this->transformer;
    }

    /**
     * @return string[]
     */
    public function supportedVariants(): array
    {
        return [
            PdfVariant::class
        ];
    }
}