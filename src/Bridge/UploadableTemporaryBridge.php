<?php

namespace Santeacademie\SuperUploaderBundle\Bridge;

use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemOperator;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Event\TemporaryVariantCreatedEvent;
use Santeacademie\SuperUploaderBundle\Event\TemporaryVariantPreCreateEvent;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Wrapper\TemporaryFile;
use Santeacademie\SuperUtil\PathUtil;
use Santeacademie\SuperUtil\StringUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadableTemporaryBridge extends AbstractUploadableBridge
{


    protected $temporaryMaxLifetime;
    protected $temporaryEntityVariantsIndex;

    public function __construct(
        string $appPublicDir,
        protected FilesystemOperator $filesystem,
        protected EventDispatcherInterface $eventDispatcher
    )
    {
        parent::__construct($appPublicDir);

        $this->temporaryMaxLifetime = 1800;
        $this->temporaryEntityVariantsIndex = [];
    }

    public function saveGenuineTemporaryFile(UploadedFile $uploadedFile): TemporaryFile
    {
        $this->removeExpiredTemporaryFiles();

        $temporaryFullName = sprintf('%s/%s',
            $this->getTemporaryPath(true),
            $this->generateTemporaryFileName($uploadedFile->guessExtension())
        );

        $temporaryFile = new TemporaryFile($temporaryFullName, false, $this->filesystem);

        $this->filesystem->write($temporaryFile->getPathname(), $uploadedFile->getContent());

        return $temporaryFile;
    }

    public function genuineToTemporaryVariantFile(
        File $genuineFile,
        AbstractVariant $variant,
        UploadableInterface $uploadableEntity,
        ?callable $transformerCallback = null
    ): TemporaryFile
    {
        $this->eventDispatcher->dispatch(new TemporaryVariantPreCreateEvent($variant, $uploadableEntity));

        $temporaryFullName = sprintf('%s/%s',
            $this->getTemporaryPath(true, $variant->getAsset()->getMediaType()),
            $this->getVariantFileName($variant, $uploadableEntity, $variant->getExtension())
        );

        $temporaryVariantFile = new TemporaryFile($temporaryFullName, false, $this->filesystem);

        // Attach temporary file to Variant
        $variant->setTemporaryFile($temporaryVariantFile);

        // Keep track of Entity-Variant relationship
        $this->indexTemporaryEntityVariant($uploadableEntity, $variant);

        // Persist temporary Variant file based on genuine one
        $this->filesystem->write($temporaryVariantFile->getPathname(), $genuineFile->getContent());

        $temporaryFile = $variant->getTemporaryFile();

        if (is_callable($transformerCallback)) {
            $temporaryFile = $transformerCallback($variant, $temporaryFile);
        }

        $this->eventDispatcher->dispatch(new TemporaryVariantCreatedEvent($variant, $uploadableEntity));

        return $temporaryFile;
    }

    private function indexTemporaryEntityVariant(UploadableInterface $uploadableEntity, AbstractVariant $variant)
    {
        if (!isset($this->temporaryEntityVariantsIndex[spl_object_hash($uploadableEntity)])) {
            $this->temporaryEntityVariantsIndex[spl_object_hash($uploadableEntity)] = [
                'uploadableEntity' => $uploadableEntity,
                'variants' => []
            ];
        }

        $this->temporaryEntityVariantsIndex[spl_object_hash($uploadableEntity)]['variants'][$variant->getName()] = $variant;
    }

    public function getIndexedEntitiesWithTemporaryVariants()
    {
        return array_map(function($entityVariants) {
            return $entityVariants['uploadableEntity'];
        }, $this->temporaryEntityVariantsIndex);
    }

    public function getIndexedEntityTemporaryVariants(UploadableInterface $uploadableEntity)
    {
        return $this->temporaryEntityVariantsIndex[spl_object_hash($uploadableEntity)]['variants'] ?? [];
    }

    public function removeVariantByEntityFromIndex(AbstractVariant $variant, UploadableInterface $uploadableEntity): bool
    {
        $variants = $this->getIndexedEntitiesWithTemporaryVariants();

        if (isset($variants[$variant->getName()])) {
            unset($this->temporaryEntityVariantsIndex[spl_object_hash($uploadableEntity)]['variants'][$variant->getName()]);
            return true;
        }

        return false;
    }

    public function removeIndexedEntityWithTemporaryVariants(UploadableInterface $uploadableEntity): bool
    {
        if (isset($this->temporaryEntityVariantsIndex[spl_object_hash($uploadableEntity)])) {
            unset($this->temporaryEntityVariantsIndex[spl_object_hash($uploadableEntity)]);
            return true;
        }

        return false;
    }





    private function removeExpiredTemporaryFiles(): void
    {
        $expiredTemporaryFiles = $this->filesystem->listContents($this->getTemporaryPath(true), true)
            ->filter(function ($file) {
                /* @var FileAttributes $file */
                return $file['type'] === 'file'
                    && preg_match('/^[^\/]*-[^\/]*\..*$/', $file['path'])
                    && time() - $file->lastModified() > $this->temporaryMaxLifetime;
            })
            ->map(function ($file) {
                return $file['path'];
            })
        ->toArray();

        foreach ($expiredTemporaryFiles as $file) {
            $this->filesystem->delete($file);
        }
    }

    private function generateTemporaryFileName(string $extension): string
    {
        return sprintf('%s-%s.%s',
            time(),
            StringUtil::generateRandomPassword(),
            $extension
        );
    }

    private function getTemporaryPath($create = false, string $leaf = '')
    {
        $dir = PathUtil::withoutTrailingSlash(sprintf('%s/%s',
            $this->getPublicDir(),
            $leaf
        ));

        if ($create) {
            $this->filesystem->createDirectory($dir);
        }

        return $dir;
    }

}