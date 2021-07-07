<?php

namespace Santeacademie\SuperUploaderBundle\Form\VariantType;

use Santeacademie\SuperUploaderBundle\Asset\Variant\OtherVariant;
use Santeacademie\SuperUploaderBundle\Form\AbstractVariantType;
use Santeacademie\SuperUploaderBundle\Interface\VariantTansformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OtherVariantType extends AbstractVariantType
{

    public function getTransformer(): ?VariantTansformerInterface
    {
        return null;
    }

    public function supportedVariants(): array
    {
        return [
            OtherVariant::class
        ];
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('js', false);
        $resolver->setDefault('css', false);
    }
}