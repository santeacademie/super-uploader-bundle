<?php

namespace Santeacademie\SuperUploaderBundle\Manager\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Santeacademie\SuperUploaderBundle\Manager\VariantEntityMapManagerInterface;
use Santeacademie\SuperUploaderBundle\Model\AbstractVariantEntityMap;

final class VariantEntityMapManager implements VariantEntityMapManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var class-string<AbstractVariantEntityMap>
     */
    private $variantEntityMapFqcn;

    /**
     * @param class-string<AbstractVariantEntityMap> $variantEntityMapFqcn
     */
    public function __construct(EntityManagerInterface $entityManager, string $variantEntityMapFqcn)
    {
        $this->entityManager = $entityManager;
        $this->variantEntityMapFqcn = $variantEntityMapFqcn;
    }

    public function find(string $identifier): ?AbstractVariantEntityMap
    {
        $repository = $this->entityManager->getRepository($this->variantEntityMapFqcn);

        return $repository->findOneBy(['fullPath' => $identifier]);
    }

    public function save(AbstractVariantEntityMap $variantEntityMap): void
    {
        $this->entityManager->persist($variantEntityMap);
        $this->entityManager->flush();
    }

    public function remove(AbstractVariantEntityMap $variantEntityMap): void
    {
        $this->entityManager->remove($variantEntityMap);
        $this->entityManager->flush();
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    
}
