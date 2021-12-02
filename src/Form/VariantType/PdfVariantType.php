<?php

namespace Santeacademie\SuperUploaderBundle\Form\VariantType;

use Santeacademie\SuperUploaderBundle\Asset\Variant\PdfVariant;
use Santeacademie\SuperUploaderBundle\Form\AbstractVariantType;
use Santeacademie\SuperUploaderBundle\Transformer\PdfTransformer;
use Santeacademie\SuperUploaderBundle\Interface\VariantTansformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('js', true);
        $resolver->setDefault('css', true);
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