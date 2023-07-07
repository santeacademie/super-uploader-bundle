<?php

namespace Santeacademie\SuperUploaderBundle\Repository;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Wrapper\SuperFile;
use Santeacademie\SuperUploaderBundle\Model\AbstractVariantEntityMap;

interface VariantEntityMapRepositoryInterface
{

    public function persistVariantEntityMap(AbstractVariant $variant, AbstractVariantEntityMap $map): void;
    
    public function deleteAnyOldEntityMap(AbstractVariant $variant, AbstractVariantEntityMap $newVariantEntityMap): void;
    
    public function deleteEntityMapByFile(SuperFile $file): void;

}
