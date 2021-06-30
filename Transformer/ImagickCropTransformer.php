<?php

namespace Santeacademie\SuperUploaderBundle\Transformer;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Super\Interfaces\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Super\Interfaces\VariantTansformerInterface;
use Symfony\Component\HttpFoundation\File\File;

class ImagickCropTransformer implements VariantTansformerInterface
{

    public function transformFile(File $file, AbstractVariant $variant, array $variantTypeData): File
    {
        $imagick = new \Imagick($file->getRealPath());

        $topLeftX = floatVal($variantTypeData['topLeftX']);
        $topLeftY = floatVal($variantTypeData['topLeftY']);
        $bottomRightX = floatVal($variantTypeData['bottomRightX']);
        $bottomRightY = floatVal($variantTypeData['bottomRightY']);

        $imagick->setImageMatte(true);
        $imagick->setImageBackgroundColor('transparent');

        $imagick->extentImage(
            $bottomRightX - $topLeftX,
            $bottomRightY - $topLeftY,
            $topLeftX,
            $topLeftY
        );

        $imagick->resizeImage(
            $variant->getWidth(),
            $variant->getHeight(),
            \Imagick::FILTER_HANNING, 
            1
        );

        $imagick->writeImage($file->getPathname());

        return $file;
    }

}