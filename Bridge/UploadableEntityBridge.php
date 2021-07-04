<?php 

namespace Santeacademie\SuperUploaderBundle\Bridge;

use App\Core\Super\Entity\ListenableEntityInterface;
use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Wrapper\FallbackResourceFile;
use Santeacademie\SuperUtil\FileUtil;
use Santeacademie\SuperUtil\IteratorUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class UploadableEntityBridge extends AbstractUploadableBridge
{

    public function __construct(
        string $appPublicDir,
        protected string $uploadableResourcesMountpoint,
        protected Filesystem $filesystem,
        protected UploadablePersistentBridge $uploadablePersistentBridge,
        protected UploadableTemporaryBridge $uploadableTemporaryBridge,
        protected EventDispatcherInterface $eventDispatcher
    )
    {
        parent::__construct($appPublicDir);
    }

    public function populateUploadableFields(UploadableInterface $uploadableEntity): void
    {
        foreach ($uploadableEntity->getLoadUploadableAssets() as $asset) {
            /** @var AbstractAsset $asset */

            if ($asset->hasPropertyScopePublic()) {
                $uploadableEntity->{$asset->getPropertyName()} = $asset;
            } else {
                $uploadableEntity->{'set'.ucfirst($asset->getPropertyName())}($asset);
            }

            foreach ($asset->getVariants() as $variant) {
                /** @var AbstractVariant $variant */
                $variantFile = $this->getEntityAssetVariantFile($uploadableEntity, $asset, $variant, true);
                $variant
                    ->setAsset($asset)
                    ->setVariantFile($variantFile)
                    ->setRequired($variant->isRequired())
                ;
            }
        }
    }

    public function getNamedEntityAssetVariantFile(
        UploadableInterface $entity,
        string $assetName,
        string $variantName,
        bool $fallbackResource = AbstractVariant::DEFAULT_FALLBACK_RESOURCE
    ): ?File
    {
        $asset = $entity->getUploadableAssetByName($assetName);
        $variant = $asset->getVariant($variantName);

        return $this->getEntityAssetVariantFile($entity, $asset, $variant, $fallbackResource);
    }

    public function getAssetVariantFallbackResourceFile(
        UploadableInterface $entity,
        AbstractAsset $asset,
        AbstractVariant $variant
    ): ?FallbackResourceFile
    {
        // trainer_profile-rectangle[randomSuffix + extension to be defined]
        $variantFileNamePrefix = $this->getVariantFileName($variant);
        $assetPath = $this->getFallbackRessourceAssetPath($entity, $asset, true);
        $fallbackResourceFile = null;

        if (is_dir($assetPath)) {
            if (($fallbackResourceFileIterator = Finder::create()
                ->in($assetPath)
                ->files()
                ->name("/^$variantFileNamePrefix/"))
                ->hasResults()
            ) {
                $fallbackResourceFile = IteratorUtil::firstFile($fallbackResourceFileIterator);

                if (!is_null($fallbackResourceFile)) {
                    $fallbackResourceFile = new FallbackResourceFile($fallbackResourceFile->getPathname());
                }
            }
        }

        return $fallbackResourceFile;
    }

    public function getEntityAssetVariantFile(
        UploadableInterface $entity,
        AbstractAsset $asset,
        AbstractVariant $variant,
        bool $fallbackResource = AbstractVariant::DEFAULT_FALLBACK_RESOURCE
    ): ?File
    {
        if (!$asset->supportsVariant($variant)) {
            throw new \LogicException(sprintf('Asset \'%s\' doesn\'t supports Variant \'%s\'',
                $asset->getName(),
                $variant->getName()
            ));
        }

        // trainer_profile-rectangle[randomSuffix + extension to be defined]
        $variantFileNamePrefix = $this->getVariantFileName($variant);
        $assetPath = $this->uploadablePersistentBridge->getUploadEntityAssetPath($entity, $asset);
        $variantFile = null;

        if (is_dir($assetPath)) {
            if (($variantFileIterator = Finder::create()
                ->in($assetPath)
                ->files()
                ->name("/^$variantFileNamePrefix/"))
                ->hasResults()
            ) {
                $variantFile = IteratorUtil::firstFile($variantFileIterator);
            }
        }

        if (is_null($variantFile) && $fallbackResource) {
            $variantFile = $this->getAssetVariantFallbackResourceFile($entity, $asset, $variant);
        }

        return $variantFile;
    }


    public function getEntityAssetVariantsFiles(UploadableInterface $entity, AbstractAsset $asset): array
    {
        return array_map(function($variant) use($entity, $asset) {
            return $this->getEntityAssetVariantFile($entity, $asset, $variant);
        }, $asset->getVariants());
    }

    public function getFallbackRessourceAssetPath(UploadableInterface $entity, AbstractAsset $asset, $create = false): string
    {
        // resources/trainer-xxxx/picture/trainer_profile
        $dir = sprintf('%s%s/%s/%s/%s',
            $this->getPublicDir(),
            $this->uploadableResourcesMountpoint,
            $entity->getUploadEntityPath(),
            $asset->getMediaType(),
            $asset->getName()
        );

        if (!is_dir($dir) && $create) {
            $this->filesystem->mkdir($dir);
        }

        return $dir;
    }

    public function directUpload(
        UploadableInterface|ListenableEntityInterface $entity,
        AbstractVariant $variant,
        File|string $fileOrBinary,
        bool $dispatchEvent = true
    ): void
    {
        if (is_string($fileOrBinary)) {
            $fileOrBinary = FileUtil::fileFromContent($fileOrBinary);
        }

        $this->uploadableTemporaryBridge->genuineToTemporaryVariantFile($fileOrBinary, $variant, $entity);
        $this->uploadablePersistentBridge->persistTemporaryVariantFiles($entity);

        if ($dispatchEvent) {
            $eventClass = $entity->getUpdatedEventClass();
            $this->eventDispatcher->dispatch(new $eventClass($entity));
        }
    }

}