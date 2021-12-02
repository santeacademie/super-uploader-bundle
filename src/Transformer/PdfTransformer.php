<?php

namespace Santeacademie\SuperUploaderBundle\Transformer;

use Imagick;
use ImagickException;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Asset\Variant\PdfVariant;
use Santeacademie\SuperUploaderBundle\Interface\VariantTansformerInterface;
use Symfony\Component\HttpFoundation\File\File;

class PdfTransformer implements VariantTansformerInterface
{
    /**
     * @throws ImagickException
     */
    public function transformFile(File $file, PdfVariant|AbstractVariant $variant, array $variantTypeData): File
    {
        if (
            $file->guessExtension() !== 'pdf'
            || (!is_null($variant->getSizeLimit()) && $file->getSize() > $variant->getSizeLimit())
        ) {
            $imagick = new Imagick($file->getRealPath());

            $imagick->writeImage($file->getPathname());
        }

        return $file;
    }
}