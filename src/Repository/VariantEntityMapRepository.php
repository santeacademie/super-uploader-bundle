<?php

namespace Santeacademie\SuperUploaderBundle\Repository;

use Santeacademie\SuperUploaderBundle\Manager\Doctrine\VariantEntityMapManager;
use Santeacademie\SuperUploaderBundle\Wrapper\SuperFile;
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

        // Build the list of columns and placeholders
        $columns = array_map(function($field) {
            return StringUtil::camelCaseToSnakeCase($field);
        }, $fields);
        $placeholders = array_fill(0, count($fields), '?');

        // Construct the SQL query
        $sql = sprintf(
            "INSERT INTO %s.%s (%s) VALUES (%s)",
            $metadata->getSchemaName(),
            $metadata->getTableName(),
            implode(',', $columns),
            implode(',', $placeholders)
        );

        // Prepare the statement
        $stmt = $em->getConnection()->prepare($sql);

        // Bind parameters
        foreach ($fields as $index => $field) {
            $value = $map->{'get'.ucfirst($field)}();
            $stmt->bindValue($index + 1, $value);
            if ($value instanceof \DateTimeInterface) {
                $stmt->bindValue($index + 1, $value->format('Y-m-d H:i:s'));
            } else {
                $stmt->bindValue($index + 1, $value);
            }
        }

        // Execute the statement
        $stmt->executeQuery();
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

    public function deleteEntityMapByFile(SuperFile $file): void
    {
        $em = $this->variantEntityMapManager->getEntityManager();

        $qb =
            $em->createQueryBuilder()
                ->delete($this->variantEntityMapManager->getEntityClass(), 'e')
                ->where('e.fullPath = :fullPath')
                ->setParameter('fullPath', $file->publicUrl())
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
