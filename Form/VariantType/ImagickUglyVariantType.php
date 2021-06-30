<?php

namespace Santeacademie\SuperUploaderBundle\Form\VariantType;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Asset\Variant\PictureVariant;
use Santeacademie\SuperUploaderBundle\Super\Interfaces\VariantTansformerInterface;
use Santeacademie\SuperUploaderBundle\Form\AbstractVariantType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImagickUglyVariantType extends AbstractVariantType implements VariantTansformerInterface
{

    public function getTransformer(): VariantTansformerInterface
    {
        return $this;
    }

    public function transformFile(File $file, AbstractVariant $variant, array $variantTypeData): File
    {
        $imagick = new \Imagick($file->getRealPath());
        $imagick->rotateimage('green', 25);
        $imagick->writeImage($file->getPathname());

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
