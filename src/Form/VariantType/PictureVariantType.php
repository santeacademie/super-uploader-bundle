<?php

namespace Santeacademie\SuperUploaderBundle\Form\VariantType;

use Santeacademie\SuperUploaderBundle\Asset\Variant\PictureVariant;
use Santeacademie\SuperUploaderBundle\Interface\VariantTansformerInterface;
use Santeacademie\SuperUploaderBundle\Form\AbstractVariantType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PictureVariantType extends AbstractVariantType
{

    public function getTransformer(): ?VariantTansformerInterface
    {
        return null;
    }

    public function supportedVariants(): array
    {
        return [
            PictureVariant::class
        ];
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('js', false);
        $resolver->setDefault('css', false);
    }

}
