<?php

namespace Santeacademie\SuperUploaderBundle\Form\VariantType;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Asset\Variant\PictureVariant;
use Santeacademie\SuperUploaderBundle\Interface\VariantTansformerInterface;
use Santeacademie\SuperUploaderBundle\Form\AbstractVariantType;
use Santeacademie\SuperUploaderBundle\Wrapper\SuperFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImagickUglyVariantType extends AbstractVariantType implements VariantTansformerInterface
{

    public function getTransformer(): VariantTansformerInterface
    {
        return $this;
    }

    public function transformFile(SuperFile $file, AbstractVariant $variant, array $variantTypeData): SuperFile
    {
        $imagick = new \Imagick($file->publicUrl());
        $imagick->rotateimage('green', 25);
        $imagick->writeImage($file->publicUrl());

        return $file;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('js', false);
        $resolver->setDefault('css', true);
    }

    public function supportedVariants(): array
    {
        return [
            PictureVariant::class
        ];
    }
}
