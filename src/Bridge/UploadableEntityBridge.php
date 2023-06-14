<?php 

namespace Santeacademie\SuperUploaderBundle\Bridge;

use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Event\PersistentVariantPreCreateEvent;
use Santeacademie\SuperUploaderBundle\Event\VariantManualUpdateRequestEvent;
use Santeacademie\SuperUploaderBundle\Exception\FileNotFoundException;
use Santeacademie\SuperUploaderBundle\Exception\PlaceholderNotFound;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Wrapper\FallbackResourceFile;
use Santeacademie\SuperUploaderBundle\Wrapper\SuperFile;
use Santeacademie\SuperUtil\FileUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;

class UploadableEntityBridge extends AbstractUploadableBridge
{

    public function __construct(
        string $appPublicDir,
        protected FilesystemOperator $resourcesFilesystem,
        protected FilesystemOperator $uploadsFilesystem,
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


    /**
     * @throws PlaceholderNotFound
     * @throws FilesystemException
     * @throws FileNotFoundException
     */
    public function getPublicUrl(
        UploadableInterface $entity,
        string $assetName,
        string $variantName,
        bool $fallbackResource = AbstractVariant::DEFAULT_FALLBACK_RESOURCE
    ): string
    {

        $file = $this->getNamedEntityAssetVariantFile($entity, $assetName, $variantName, $fallbackResource);

        if (!$file) {
            throw new FileNotFoundException();
        }

        if ($this->uploadsFilesystem->fileExists($file->getPathname())) {
            return $this->uploadsFilesystem->publicUrl($file->getPathname());
        }

        if ($this->resourcesFilesystem->fileExists($file->getPathname())) {
            return $this->resourcesFilesystem->publicUrl($file->getPathname());
        }

        throw new PlaceholderNotFound();
    }

    public function getNamedEntityAssetVariantFile(
        UploadableInterface $entity,
        string $assetName,
        string $variantName,
        bool $fallbackResource = AbstractVariant::DEFAULT_FALLBACK_RESOURCE
    ): ?SuperFile
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

        if ($this->resourcesFilesystem->directoryExists($assetPath)) {
            if ($fallbackResourceFileIterator = $this->resourcesFilesystem->listContents($assetPath)
                ->filter(fn(StorageAttributes $attributes) => str_contains($attributes->path(), $variantFileNamePrefix))
                ->toArray()
            ) {
                /* @var FileAttributes $fallbackResourceFile */
                $fallbackResourceFile = current($fallbackResourceFileIterator);

                if (!is_null($fallbackResourceFile)) {
                    $fallbackResourceFile = new FallbackResourceFile($fallbackResourceFile->path(), false, $this->resourcesFilesystem);
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
    ): ?SuperFile
    {
        if (!$asset->supportsVariant($variant)) {
            throw new \LogicException(sprintf('Asset \'%s\' doesn\'t supports Variant \'%s\'',
                $asset->getName(),
                $variant->getName()
            ));
        }

        // trainer_profile-rectangle[randomSuffix + extension to be defined]
        $variantFileNamePrefix = $this->getVariantFileName($variant, '', '-');
        $variantFileNamePrefix = preg_replace('#(.+)--$#', "$1-", $variantFileNamePrefix);
        $assetPath = $this->uploadablePersistentBridge->getUploadEntityAssetPath($entity, $asset);
        $variantFile = null;

        if ($this->uploadsFilesystem->has($assetPath)) {
            $variantFileIterator = $this->uploadsFilesystem->listContents($assetPath, true);
            foreach ($variantFileIterator as $foundVariantFile) {
                if ($foundVariantFile['type'] === 'file' && str_starts_with($foundVariantFile->path(), $assetPath . '/' . $variantFileNamePrefix)) {
                    $variantFile = new SuperFile($foundVariantFile->path(), true, $this->uploadsFilesystem);
                    break;
                }
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
        $dir = sprintf('%s/%s/%s',
            $entity->getUploadEntityPath(),
            $asset->getMediaType(),
            $asset->getName()
        );

        if (!is_dir($dir) && $create) {
            $this->resourcesFilesystem->createDirectory($dir);
        }

        return $dir;
    }

    public function manualUpload(
        UploadableInterface $entity,
        AbstractVariant $variant,
        File|string $fileOrBinary,
        bool $flush = true,
        ?callable $transformerCallback = null
    ): void
    {
        if (is_string($fileOrBinary)) {
            $fileOrBinary = FileUtil::fileFromContent($fileOrBinary);
        }

        $temporaryVariantFile = $this->uploadableTemporaryBridge->genuineToTemporaryVariantFile($fileOrBinary, $variant, $entity, $transformerCallback);

        $oldValue = $this->getEntityAssetVariantFile(
            entity: $entity,
            asset: $variant->getAsset(),
            variant: $variant,
            fallbackResource: false
        );

        $this->eventDispatcher->dispatch(new VariantManualUpdateRequestEvent($variant, $entity, $oldValue, $temporaryVariantFile));

        if ($flush) {
            $this->eventDispatcher->dispatch(new PersistentVariantPreCreateEvent($variant, $entity));
        }
    }

}
