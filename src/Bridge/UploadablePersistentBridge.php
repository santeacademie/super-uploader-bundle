<?php 

namespace Santeacademie\SuperUploaderBundle\Bridge;

use ErrorException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use Santeacademie\SuperUploaderBundle\Asset\Variant\PictureVariant;
use Santeacademie\SuperUploaderBundle\Event\PersistentVariantCreatedEvent;
use Santeacademie\SuperUploaderBundle\Event\PersistentVariantDeletedEvent;
use Santeacademie\SuperUploaderBundle\Model\AbstractVariantEntityMap;
use Santeacademie\SuperUploaderBundle\Model\VariantEntityMap;
use Santeacademie\SuperUploaderBundle\Repository\VariantEntityMapRepository;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Doctrine\ORM\EntityManagerInterface;
use Santeacademie\SuperUtil\PathUtil;
use Santeacademie\SuperUtil\StringUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Santeacademie\SuperUploaderBundle\Wrapper\SuperFile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UploadablePersistentBridge extends AbstractUploadableBridge
{

    protected $uploadableEntitiesWithDeletableVariantsIndex;

    public function __construct(
        string $appPublicDir,
        protected FilesystemOperator $filesystem,
        protected UploadableTemporaryBridge $uploadableTemporaryBridge,
        protected ?VariantEntityMapRepository $variantEntityMapRepository,
        protected EventDispatcherInterface $eventDispatcher
    )
    {
        parent::__construct($appPublicDir);

        $this->uploadableEntitiesWithDeletableVariantsIndex = [];
    }

    // Move TemporaryAssetVariant files to AssetVariant (definitive persisted mountpoint)
    public function persistTemporaryVariantFile(AbstractVariant $variant, UploadableInterface $uploadableEntity): AbstractVariant
    {
        $asset = $variant->getAsset();
        $entityAssetPath = $this->getUploadEntityAssetPath($uploadableEntity, $asset, true);
        $variantFileNamePrefix = $this->getVariantFileName($variant);

        //variantFileName = $this->getVariantFileName($variant, $variant->getTemporaryFile()->guessExtension(), StringUtil::generateRandomPassword());
        // Reuse old temporary name (important)
        $variantFileName = $variant->getTemporaryFile()->getFilename();

        if (empty($variant->getExtension())) {
            $variantFileName .= '.'.$variant->getTemporaryFile()->guessExtension();
        }

        // Move temporary Variant file in Asset path
        $variantFile = new SuperFile(sprintf('%s/%s', $entityAssetPath, $variantFileName), false, $this->filesystem);

        $filesToDelete = $this->filesystem->listContents($entityAssetPath)
            ->filter(fn (StorageAttributes $attributes) => str_starts_with($attributes->path(), $entityAssetPath . '/' . $variantFileNamePrefix))
            ->map(fn (StorageAttributes $attributes) => $attributes->path())
            ->toArray();

        foreach ($filesToDelete as $file) {
            $this->filesystem->delete($file);
        }


        $this->filesystem->write($variantFile->getPathname(), $variant->getTemporaryFile()->getContent());


        // Delete temporary artifacts (related to @ShowNoTransformation)
        //$this->filesystem->remove($variant->getTemporaryFile());

        // Link Variant file path to its object
        $variant->setVariantFile($variantFile);

        // Keep an eye on this variant on a database
        if (!empty($this->variantEntityMapRepository)) {
            $this->variantEntityMapRepository->persistVariantEntityMap(
                $variant,
                $this->generateVariantEntityMap($uploadableEntity, $variant)
            );
        }

        $this->eventDispatcher->dispatch(new PersistentVariantCreatedEvent($variant, $uploadableEntity));

        return $variant;
    }

    // Move TemporaryAssetVariant files to AssetVariant in bulk (definitive persisted mountpoint)
    public function persistTemporaryVariantFiles(UploadableInterface $uploadableEntity): array
    {
        return array_map(function($variant) use($uploadableEntity) {
            /** @var AbstractVariant $variant */
            return $this->persistTemporaryVariantFile($variant, $uploadableEntity)->getVariantFile();
        }, $this->uploadableTemporaryBridge->getIndexedEntityTemporaryVariants($uploadableEntity));
    }

    public function generateVariantEntityMap(UploadableInterface $uploadableEntity, AbstractVariant $variant): AbstractVariantEntityMap
    {
        $asset = $variant->getAsset();
        $variantFile = $variant->getVariantFile(false);

        $variantEntityMap = (new VariantEntityMap())
            ->setEntityClass(PathUtil::sanitizeForProxy(get_class($uploadableEntity)))
            ->setEntityIdentifier("".$uploadableEntity->getUploadableKeyValue())
            ->setFullPath($variantFile->getPathname())
            ->setAssetName($asset->getName())
            ->setVariantName($variant->getName())
            ->setMediaType($asset->getMediaType())
            ->setAssetClass(get_class($asset))
            ->setVariantClass(get_class($variant))
            ->setVariantTypeClass($variant->getVariantTypeClass())
            ->setFileExtension($variantFile->guessExtension())
            ->setFileSize((float) $variantFile->getSize())
        ;

        if (property_exists($variant, 'width')) {
            $variantEntityMap->setPictureWidth($variant->getWidth());
        }

        if (property_exists($variant, 'height')) {
            $variantEntityMap->setPictureHeight($variant->getHeight());
        }

        return $variantEntityMap;
    }

    public function removeEntityVariantFiles(UploadableInterface $uploadableEntity): bool
    {
        if (!$this->isEntityIndexedForDeletableVariants($uploadableEntity)) {
            return false;
        }

        foreach ($uploadableEntity->getLoadUploadableAssets() as $asset) {
            /** @var AbstractAsset $asset */

            foreach ($asset->getVariants() as $variant) {
                $this->removeEntityVariantFile($variant, $uploadableEntity);
            }
        }

        // Alternatively...
        /*
        if (!empty($this->variantEntityMapRepository)) {
            $this->variantEntityMapRepository->deleteEntityMapByUploadableEntity($uploadableEntity);
        }
        */

        return true;
    }

    public function removeEntityVariantFile(AbstractVariant $variant, UploadableInterface $uploadableEntity): bool
    {
        if (!$this->isEntityIndexedForDeletableVariants($uploadableEntity)) {
            return false;
        }

        $file = $variant->getVariantFile(false);

        if (is_null($file)) {
            return false;
        }

        if (!empty($this->variantEntityMapRepository)) {
            $this->variantEntityMapRepository->deleteEntityMapByFile($file);
        }

        $this->filesystem->delete($file);
        $variant->setVariantFile(null);

        $this->eventDispatcher->dispatch(new PersistentVariantDeletedEvent($variant, $uploadableEntity));

        return true;
    }

    public function indexEntityVariantsToDelete(UploadableInterface $uploadableEntity): void
    {
        $this->uploadableEntitiesWithDeletableVariantsIndex[$uploadableEntity->getUploadableKeyValue()] = $uploadableEntity;
    }

    public function getIndexedEntitiesWithDeletableVariants(): array
    {
        return array_values($this->uploadableEntitiesWithDeletableVariantsIndex);
    }

    public function isEntityIndexedForDeletableVariants(UploadableInterface $uploadableEntity): bool
    {
        return isset($this->uploadableEntitiesWithDeletableVariantsIndex[$uploadableEntity->getUploadableKeyValue()]);
    }

    public function removeIndexedEntityWithDeletableVariants(UploadableInterface $uploadableEntity): bool
    {
        if (isset($this->uploadableEntitiesWithDeletableVariantsIndex[$uploadableEntity->getUploadableKeyValue()])) {
            unset($this->uploadableEntitiesWithDeletableVariantsIndex[$uploadableEntity->getUploadableKeyValue()]);
            return true;
        }

        return false;
    }

    public function getUploadEntityAssetPath(UploadableInterface $entity, AbstractAsset $asset, $create = false): string
    {
        // entity-xxxx/{1/2/3}/picture/profile_picture
        $dir = sprintf('%s/%s/%s/%s',
            $entity->getUploadEntityPath(),
            $entity->getUploadEntityToken(),
            $asset->getMediaType(),
            $asset->getName()
        );

        if ($create) {
            $this->filesystem->createDirectory($dir);
        }

        return $dir;
    }


}
