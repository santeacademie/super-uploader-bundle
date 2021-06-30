<?php

namespace Santeacademie\SuperUploaderBundle\Repository;

use Santeacademie\SuperUploaderBundle\Manager\Doctrine\VariantEntityMapManager;
use Santeacademie\SuperUtil\StringUtil;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Entity\VariantEntityMap as VariantEntityMapEntity;
use Santeacademie\SuperUploaderBundle\Manager\VariantEntityMapManagerInterface;
use Santeacademie\SuperUploaderBundle\Model\AbstractVariantEntityMap;
use Symfony\Component\HttpFoundation\File\File;

final class VariantEntityMapRepository implements VariantEntityMapRepositoryInterface
{
    /**
     * @var VariantEntityMapManagerInterface
     */
    private $variantEntityMapManager;

    public function __construct(VariantEntityMapManager $variantEntityMapManager)
    {
        $this->variantEntityMapManager = $variantEntityMapManager;
    }

    public function persistVariantEntityMap(AbstractVariant $variant, VariantEntityMap $map): void
    {
        $this->deleteAnyOldEntityMap($variant, $map);

        $em = $this->variantEntityMapManager->getEntityManager();
        $fields = $em->getClassMetadata(VariantEntityMap::class)->getFieldNames();

        $sql = sprintf("INSERT INTO %s.%s(%s) VALUES(%s)",
            $this->getClassMetadata()->getSchemaName(),
            $this->getClassMetadata()->getTableName(),
            implode(',', array_map(function($field) {
                return StringUtil::camelCaseToSnakeCase($field);
            }, $fields)),
            implode(',', array_map(function($field) use($map) {
                $value = $map->{'get'.ucfirst($field)}();

                if (is_numeric($value)) {
                    return $value;
                } elseif (is_null($value)) {
                    return "null";
                } elseif ($value instanceof \DateTimeInterface) {
                    return sprintf("'%s'", $value->format('Y-m-d H:i:s'));
                }

                return sprintf("'%s'", pg_escape_string($value));
            }, $fields))
        );

        $this->getEntityManager()->getConnection()->executeStatement($sql);
    }

    public function deleteAnyOldEntityMap(AbstractVariant $variant, VariantEntityMap $newVariantEntityMap): void
    {
        $em = $this->variantEntityMapManager->getEntityManager();

        $qb =
            $em->createQueryBuilder()
                ->delete(VariantEntityMap::class, 'e')
                ->where('e.assetName = :assetName')->setParameter('assetName', $variant->getAsset()->getName())
                ->andWhere('e.variantName = :variantName')->setParameter('variantName', $variant->getName())
                ->andWhere('e.mediaType = :mediaType')->setParameter('mediaType', $variant->getAsset()->getMediaType())
                ->andWhere('e.entityClass = :entityClass')
                ->andWhere('e.entityIdentifier = :entityIdentifier')
                ->setParameter('entityIdentifier', $newVariantEntityMap->getEntityIdentifier())
                ->setParameter('entityClass', $newVariantEntityMap->getEntityClass())
        ;

        $qb->getQuery()->getResult();
    }

    public function deleteEntityMapByFile(File $file): void
    {
        $em = $this->variantEntityMapManager->getEntityManager();

        $qb =
            $em->createQueryBuilder()
                ->delete(VariantEntityMap::class, 'e')
                ->where('e.fullPath = :fullPath')
                ->setParameter('fullPath', $file->getPathname())
        ;

        $qb->getQuery()->getResult();
    }

}
