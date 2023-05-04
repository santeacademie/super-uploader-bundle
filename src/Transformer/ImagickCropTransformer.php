<?php

namespace Santeacademie\SuperUploaderBundle\Transformer;

use League\Flysystem\FilesystemOperator;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Interface\VariantTansformerInterface;
use Symfony\Component\HttpFoundation\File\File;

class ImagickCropTransformer implements VariantTansformerInterface
{

    public function __construct(private FilesystemOperator $filesystemOperator)
    {
    }

    public function transformFile(File $file, AbstractVariant $variant, array $variantTypeData): File
    {
        $imagick = new \Imagick();
        $imagick->readImageBlob($file->getContent());

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

        $this->filesystemOperator->write($file->getPathname(), $imagick->getImageBlob());

        return $file;
    }

}