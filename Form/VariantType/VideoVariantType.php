<?php

namespace Santeacademie\SuperUploaderBundle\Form\VariantType;

use Santeacademie\SuperUploaderBundle\Asset\Variant\VideoVariant;
use Santeacademie\SuperUploaderBundle\Super\Interfaces\VariantTansformerInterface;
use Santeacademie\SuperUploaderBundle\Form\AbstractVariantType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VideoVariantType extends AbstractVariantType
{

    public function getTransformer(): ?VariantTansformerInterface
    {
        return null;
    }

    public function getParent()
    {
        return DocumentVariantType::class;
    }

    public function supportedVariants(): array
    {
        return [
            VideoVariant::class
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('js', false);
        $resolver->setDefault('css', false);
    }

}
