<?php

namespace Santeacademie\SuperUploaderBundle\Persistence\Mapping;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Santeacademie\SuperUploaderBundle\Model\VariantEntityMap;
use Santeacademie\SuperUploaderBundle\Model\AbstractVariantEntityMap;

class Driver implements MappingDriver
{
    /**
     * @var bool
     */
    private $withCustomVariantEntityMapClass;

    public function __construct(bool $withCustomVariantEntityMapClass)
    {
        $this->withCustomVariantEntityMapClass = $withCustomVariantEntityMapClass;
    }

    public function loadMetadataForClass($className, ClassMetadata $metadata): void
    {
        switch ($className) {
            case AbstractVariantEntityMap::class:
                $this->buildAbstractVariantEntityMapMetadata($metadata);
                break;
            case VariantEntityMap::class:
                $this->buildVariantEntityMapMetadata($metadata);
                break;
            default:
                throw new \RuntimeException(sprintf('%s cannot load metadata for class %s', __CLASS__, $className));
        }
    }

    public function getAllClassNames(): array
    {
        return array_merge(
            [
                VariantEntityMap::class
            ],
            $this->withCustomVariantEntityMapClass ? [] : [VariantEntityMap::class]
        );
    }

    public function isTransient($className): bool
    {
        return AbstractVariantEntityMap::class !== $className;
    }

    private function buildVariantEntityMapMetadata(ClassMetadata $metadata): void
    {
        (new ClassMetadataBuilder($metadata))
            ->getClassMetadata()->setPrimaryTable([
                'name' => 'super_uploader_variant_entity_map',
                'schema' => 'santeacademie'
            ])
        ;
    }

    private function buildAbstractVariantEntityMapMetadata(ClassMetadata $metadata): void
    {
        (new ClassMetadataBuilder($metadata))
            ->setMappedSuperClass()
            ->createField('fullPath', 'string')->makePrimaryKey()->length(256)->option('fixed', false)->build()
            ->createField('entityClass', 'string')->nullable(false)->length(256)->option('fixed', false)->build()
            ->createField('entityIdentifier', 'string')->nullable(false)->length(128)->option('fixed', false)->build()
            ->createField('assetName', 'string')->nullable(false)->length(128)->option('fixed', false)->build()

            ->createField('variantName', 'string')->nullable(false)->length(128)->option('fixed', false)->build()

            ->createField('mediaType', 'string')->nullable(false)->length(128)->option('fixed', false)->build()
            ->createField('assetClass', 'string')->nullable(true)->length(256)->option('fixed', false)->build()
            ->createField('variantClass', 'string')->nullable(true)->length(256)->option('fixed', false)->build()
            ->createField('variantTypeClass', 'string')->nullable(true)->length(256)->option('fixed', false)->build()
            ->createField('fileExtension', 'string')->nullable(true)->length(128)->option('fixed', false)->build()

            ->createField('fileSize', 'decimal')->nullable(true)->precision(10)->scale(0)->build()
            ->createField('pictureWidth', 'decimal')->nullable(true)->precision(10)->scale(0)->build()
            ->createField('pictureHeight', 'decimal')->nullable(true)->precision(10)->scale(0)->build()
            ->createField('createdAt', 'datetimetz')->nullable(false)->option('default', 'CURRENT_TIMESTAMP')->build()
        ;
    }


}