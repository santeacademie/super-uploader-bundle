<?php

namespace Santeacademie\SuperUploaderBundle\Manager;

use Santeacademie\SuperUploaderBundle\Model\AbstractVariantEntityMap;

interface VariantEntityMapManagerInterface
{
    public function find(string $identifier): ?AbstractVariantEntityMap;

    public function save(AbstractVariantEntityMap $variantEntityMap): void;

    public function remove(AbstractVariantEntityMap $variantEntityMap): void;
}