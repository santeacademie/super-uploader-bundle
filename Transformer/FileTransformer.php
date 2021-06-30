<?php

namespace Santeacademie\SuperUploaderBundle\Transformer;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Super\Interfaces\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Super\Interfaces\VariantTansformerInterface;
use Symfony\Component\HttpFoundation\File\File;

class FileTransformer implements VariantTansformerInterface
{

    public function transformFile(File $file, AbstractVariant $variant, array $variantTypeData): File
    {
        return $file;
    }

}