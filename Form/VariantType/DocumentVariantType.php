<?php

namespace Santeacademie\SuperUploaderBundle\Form\VariantType;

use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Asset\Variant\DocumentVariant;
use Santeacademie\SuperUploaderBundle\Super\Interfaces\VariantTansformerInterface;
use Santeacademie\SuperUploaderBundle\Form\AbstractVariantType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentVariantType extends AbstractVariantType
{

    public function getTransformer(): ?VariantTansformerInterface
    {
        return null;
    }

    public function supportedVariants(): array
    {
        return [
            DocumentVariant::class
        ];
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('js', false);
        $resolver->setDefault('css', false);
    }

}
