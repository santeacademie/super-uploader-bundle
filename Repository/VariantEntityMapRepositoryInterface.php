<?php

namespace Santeacademie\SuperUploaderBundle\Repository;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Symfony\Component\HttpFoundation\File\File;
use Santeacademie\SuperUploaderBundle\Model\AbstractVariantEntityMap;

interface VariantEntityMapRepositoryInterface
{

    public function persistVariantEntityMap(AbstractVariant $variant, AbstractVariantEntityMap $map): void;
    
    public function deleteAnyOldEntityMap(AbstractVariant $variant, AbstractVariantEntityMap $newVariantEntityMap): void;
    
    public function deleteEntityMapByFile(File $file): void;

}
