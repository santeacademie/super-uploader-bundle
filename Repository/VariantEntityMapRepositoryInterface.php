<?php

namespace Santeacademie\SuperUploaderBundle\Repository;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Symfony\Component\HttpFoundation\File\File;

interface VariantEntityMapRepositoryInterface
{

    public function persistVariantEntityMap(AbstractVariant $variant, VariantEntityMap $map): void;
    
    public function deleteAnyOldEntityMap(AbstractVariant $variant, VariantEntityMap $newVariantEntityMap): void;
    
    public function deleteEntityMapByFile(File $file): void;

}
