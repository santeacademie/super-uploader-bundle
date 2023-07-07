<?php

namespace Santeacademie\SuperUploaderBundle\Transformer;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Interface\VariantTansformerInterface;
use Santeacademie\SuperUploaderBundle\Wrapper\SuperFile;

class FileTransformer implements VariantTansformerInterface
{

    public function transformFile(SuperFile $file, AbstractVariant $variant, array $variantTypeData): SuperFile
    {
        return $file;
    }

}