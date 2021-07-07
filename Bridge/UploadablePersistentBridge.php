<?php 

namespace Santeacademie\SuperUploaderBundle\Bridge;

use Santeacademie\SuperUploaderBundle\Interface\StaticExtensionVariantInterface;
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
use Symfony\Component\HttpFoundation\File\File;

class UploadablePersistentBridge extends AbstractUploadableBridge
{

    protected $uploadableEntitiesWithDeletableVariantsIndex;

    public function __construct(
        string $appPublicDir,
        protected string $uploadableMountpoint,
        protected Filesystem $filesystem,
        protected UploadableTemporaryBridge $uploadableTemporaryBridge,
        protected ?VariantEntityMapRepository $variantEntityMapRepository
    )
    {
        parent::__construct($appPublicDir);

        $this->uploadableEntitiesWithDeletableVariantsIndex = [];
    }

    // Move TemporaryAssetVariant files to AssetVariant (definitive persisted mountpoint)
    public function persistTemporaryVariantFiles(UploadableInterface $uploadableEntity): array
    {
        return array_map(function($variant) use($uploadableEntity) {
            /** @var AbstractVariant $variant */

            $asset = $variant->getAsset();
            $entityAssetPath = $this->getUploadEntityAssetPath($uploadableEntity, $asset, true);
            $variantFileNamePrefix = $this->getVariantFileName($variant);

            //variantFileName = $this->getVariantFileName($variant, $variant->getTemporaryFile()->guessExtension(), StringUtil::generateRandomPassword());
            // Reuse old temporary name (important)
            $variantFileName = $variant->getTemporaryFile()->getFilename();

            if (!$variant instanceof StaticExtensionVariantInterface) {
                $variantFileName .= '.'.$variant->getTemporaryFile()->guessExtension();
            }

            $variantFile = new File(sprintf('%s/%s', $entityAssetPath, $variantFileName), false);

            // Delete old Variant file in Asset path
            $this->filesystem->remove(Finder::create()->in($entityAssetPath)->files()->name("/^$variantFileNamePrefix/"));

            // Move temporary Variant file in Asset path
            $this->filesystem->copy($variant->getTemporaryFile(), $variantFile);

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

            return $variantFile;
        }, $this->uploadableTemporaryBridge->getIndexedEntityTemporaryVariants($uploadableEntity));
    }

    public function generateVariantEntityMap(UploadableInterface $uploadableEntity, AbstractVariant $variant): AbstractVariantEntityMap
    {
        $asset = $variant->getAsset();
        $variantFile = $variant->getVariantFile(false);

        $variantEntityMap = (new VariantEntityMap())
            ->setEntityClass(PathUtil::sanitizeForProxy(get_class($uploadableEntity)))
            ->setEntityIdentifier("".$uploadableEntity->getEntityIdentifierValue())
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
                /** @var AbstractVariant $variant */
                $file = $variant->getVariantFile(false);
                
                if (is_null($file)) {
                    continue;
                }

                if (!empty($this->variantEntityMapRepository)) {
                    $this->variantEntityMapRepository->deleteEntityMapByFile($file);
                }

                $this->filesystem->remove($file);
                $variant->setVariantFile(null);
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

    public function indexEntityVariantsToDelete(UploadableInterface $uploadableEntity): void
    {
        $this->uploadableEntitiesWithDeletableVariantsIndex[$uploadableEntity->getEntityIdentifierValue()] = $uploadableEntity;
    }

    public function getIndexedEntitiesWithDeletableVariants(): array
    {
        return array_values($this->uploadableEntitiesWithDeletableVariantsIndex);
    }

    public function isEntityIndexedForDeletableVariants(UploadableInterface $uploadableEntity): bool
    {
        return isset($this->uploadableEntitiesWithDeletableVariantsIndex[$uploadableEntity->getEntityIdentifierValue()]);
    }

    public function removeIndexedEntityWithDeletableVariants(UploadableInterface $uploadableEntity): bool
    {
        if (isset($this->uploadableEntitiesWithDeletableVariantsIndex[$uploadableEntity->getEntityIdentifierValue()])) {
            unset($this->uploadableEntitiesWithDeletableVariantsIndex[$uploadableEntity->getEntityIdentifierValue()]);
            return true;
        }

        return false;
    }

    public function getUploadEntityAssetPath(UploadableInterface $entity, AbstractAsset $asset, $create = false): string
    {
        // uploads/trainer-xxxx/{1/2/3}/picture/trainer_profile
        $dir = sprintf('%s%s/%s/%s/%s/%s',
            $this->getPublicDir(),
            $this->uploadableMountpoint,
            $entity->getUploadEntityPath(),
            $entity->getUploadEntityToken(),
            $asset->getMediaType(),
            $asset->getName()
        );

        if (!is_dir($dir) && $create) {
            $this->filesystem->mkdir($dir);
        }

        return $dir;
    }


}