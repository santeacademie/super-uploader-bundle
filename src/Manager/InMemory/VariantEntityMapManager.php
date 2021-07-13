<?php

namespace Santeacademie\SuperUploaderBundle\Manager\InMemory;

use Santeacademie\SuperUploaderBundle\Model\AbstractVariantEntityMap;

final class VariantEntityMapManager
{
    /**
     * @var array<string, AbstractVariantEntityMap>
     */
    private $variantEntityMaps = [];

    public function find(string $identifier): ?AbstractVariantEntityMap
    {
        return $this->variantEntityMaps[$identifier] ?? null;
    }

    public function save(AbstractVariantEntityMap $variantEntityMap): void
    {
        $this->variantEntityMaps[$variantEntityMap->getIdentifier()] = $variantEntityMap;
    }

    public function remove(AbstractVariantEntityMap $variantEntityMap): void
    {
        unset($this->variantEntityMaps[$variantEntityMap->getIdentifier()]);
    }

}
