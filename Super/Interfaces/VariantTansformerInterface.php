<?php

namespace Santeacademie\SuperUploaderBundle\Super\Interfaces;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Symfony\Component\HttpFoundation\File\File;

interface VariantTansformerInterface
{

    public function transformFile(File $file, AbstractVariant $variant, array $variantTypeData): File;

}