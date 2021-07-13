<?php

namespace Santeacademie\SuperUploaderBundle\Repository;

use Santeacademie\SuperUploaderBundle\Manager\Doctrine\VariantEntityMapManager;
use Santeacademie\SuperUtil\StringUtil;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Manager\VariantEntityMapManagerInterface;
use Santeacademie\SuperUploaderBundle\Model\AbstractVariantEntityMap;
use Santeacademie\SuperUploaderBundle\Model\VariantEntityMap;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;

use Symfony\Component\HttpFoundation\File\File;

final class VariantEntityMapRepository implements VariantEntityMapRepositoryInterface
{


    public function __construct(protected VariantEntityMapManager $variantEntityMapManager)
    {

    }

    public function persistVariantEntityMap(AbstractVariant $variant, AbstractVariantEntityMap $map): void
    {
        $this->deleteAnyOldEntityMap($variant, $map);

        $em = $this->variantEntityMapManager->getEntityManager();
        $metadata = $em->getClassMetadata(get_class($map));
     
        $fields = $metadata->getFieldNames();

        $sql = sprintf("INSERT INTO %s.%s(%s) VALUES(%s)",
            $metadata->getSchemaName(),
            $metadata->getTableName(),
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

        $em->getConnection()->executeStatement($sql);
    }

    public function deleteAnyOldEntityMap(AbstractVariant $variant, AbstractVariantEntityMap $newVariantEntityMap): void
    {
        $em = $this->variantEntityMapManager->getEntityManager();

        $qb =
            $em->createQueryBuilder()
                ->delete(get_class($newVariantEntityMap), 'e')
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
                ->delete($this->variantEntityMapManager->getEntityClass(), 'e')
                ->where('e.fullPath = :fullPath')
                ->setParameter('fullPath', $file->getPathname())
        ;

        $qb->getQuery()->getResult();
    }

    public function deleteEntityMapByUploadableEntity(UploadableInterface $uploadableEntity): void
    {
        $em = $this->variantEntityMapManager->getEntityManager();

        $qb =
            $em->createQueryBuilder()
                ->delete($this->variantEntityMapManager->getEntityClass(), 'e')
                ->where('e.entityClass = :entityClass')
                ->andWhere('e.entityIdentifier = :entityIdentifier')
                ->setParameter('entityClass', get_class($uploadableEntity))
                ->setParameter('entityIdentifier', $uploadableEntity->getUploadableKeyValue())
        ;

        $qb->getQuery()->getResult();
    }

}
