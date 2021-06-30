<?php 

namespace Santeacademie\SuperUploaderBundle\Bridge;

use Santeacademie\SuperUploaderBundle\Super\Wrapper\TemporaryFile;
use Santeacademie\SuperUploaderBundle\Super\Interfaces\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUtil\PathUtil;
use Santeacademie\SuperUtil\StringUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadableTemporaryBridge extends AbstractUploadableBridge
{


    protected $temporaryMaxLifetime;
    protected $temporaryEntityVariantsIndex;

    public function __construct(
        string $appPublicDir,
        protected string $uploadableTemporaryMountpoint,
        protected Filesystem $filesystem
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

        $temporaryFile = new TemporaryFile($temporaryFullName, false);

        $this->filesystem->copy($uploadedFile, $temporaryFile);

        return $temporaryFile;
    }

    public function genuineToTemporaryVariantFile(
        File $genuineFile,
        AbstractVariant $variant,
        UploadableInterface $uploadableEntity
    ): TemporaryFile
    {
        $temporaryFullName = sprintf('%s/%s',
            $this->getTemporaryPath(true, $variant->getAsset()->getMediaType()),
            $this->getVariantFileName($variant, $variant->getExtension(), StringUtil::generateRandomPassword())
        );

        $temporaryVariantFile = new TemporaryFile($temporaryFullName, false);

        // Attach temporary file to Variant
        $variant->setTemporaryFile($temporaryVariantFile);

        // Keep track of Entity-Variant relationship
        $this->indexTemporaryEntityVariant($uploadableEntity, $variant);

        // Persist temporary Variant file based on genuine one
        $this->filesystem->copy($genuineFile, $temporaryVariantFile);

        return $variant->getTemporaryFile();
    }

    private function indexTemporaryEntityVariant(UploadableInterface $uploadableEntity, AbstractVariant $variant)
    {
        if (!isset($this->temporaryEntityVariantsIndex[spl_object_hash($uploadableEntity)])) {
            $this->temporaryEntityVariantsIndex[spl_object_hash($uploadableEntity)] = [
                'uploadableEntity' => $uploadableEntity,
                'variants' => []
            ];
        }

        $this->temporaryEntityVariantsIndex[spl_object_hash($uploadableEntity)]['variants'][] = $variant;
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
        $expiredTemporaryFiles = Finder::create()
            ->in($this->getTemporaryPath(true))
            ->files()
            ->name('*-*.*')
            ->filter(function(\SplFileInfo $file) {
                return time() - $file->getCTime() > $this->temporaryMaxLifetime;
            })
        ;

        $this->filesystem->remove($expiredTemporaryFiles);
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
        $dir = PathUtil::withoutTrailingSlash(sprintf('%s%s/%s',
            $this->getPublicDir(),
            $this->uploadableTemporaryMountpoint,
            $leaf
        ));

        if ($create) {
            $this->filesystem->mkdir($dir);
        }

        return $dir;
    }

}