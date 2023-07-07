<?php

namespace Santeacademie\SuperUploaderBundle\Interface;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Wrapper\SuperFile;

interface VariantTansformerInterface
{

    public function transformFile(SuperFile $file, AbstractVariant $variant, array $variantTypeData): SuperFile;

}