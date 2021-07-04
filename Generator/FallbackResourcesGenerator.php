<?php

namespace Santeacademie\SuperUploaderBundle\Generator;

use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Asset\Variant\PictureVariant;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableEntityBridge;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\KernelInterface;

class FallbackResourcesGenerator
{

    protected $font;

    public function __construct(
        protected string $appPublicDir,
        protected Filesystem $filesystem,
        protected EntityManagerInterface $entityManager,
        protected KernelInterface $kernel,
        protected UploadableEntityBridge $uploadableEntityBridge
    )
    {
        $this->font = $this->appPublicDir.'/fonts/verdana.ttf';
    }

    private function generatePictureVariantResource(UploadableInterface $entity, PictureVariant $variant): File
    {
        $assetPath = $this->getFallbackRessourceAssetPath($entity, $variant->getAsset(), true);
        $variantResourceFileName = $this->uploadableEntityBridge->getVariantFileName($variant, 'png');
        $resourceFile = new File(sprintf('%s/%s', $assetPath, $variantResourceFileName), false);

        $fontSize = $variant->getWidth() / 12;
        $angle = 0;
        $font = $this->getFontPath();
        $text = $variant->getWidth().' x '.$variant->getHeight();

        // Get size of text
        list($left, $bottom, $right, , , $top) = imageftbbox($fontSize, $angle, $font, $text);

        // Generate text coordinates
        $x = $variant->getWidth() / 2 - (($right - $left) / 2);
        $y = $variant->getHeight() / 2 - (($bottom + $top) / 2);

        $im = imagecreate($variant->getWidth(), $variant->getHeight());
        imagerectangle($im, 0, 0, $variant->getWidth(), $variant->getHeight(), imagecolorallocate($im, 170, 170, 170));
        imagettftext($im, $fontSize, $angle, $x, $y, imagecolorallocate($im, 50, 50, 50), $font, $text);
        imagepng($im, $resourceFile->getPathname());
        imagedestroy($im);

        return $resourceFile;
    }

    public function generateAllResources(bool $reset = false)
    {
        $files = [];

        foreach($this->entityManager->getMetadataFactory()->getAllMetadata() as $meta) {
            $name = $meta->getName();

            try {
                $object = new $name();
            } catch(\ArgumentCountError $e) {
                continue;
            }

            if (!$object instanceof UploadableInterface) {
                continue;
            }

            $files[$name] = [];

            foreach ($object->getLoadUploadableAssets() as $asset) {
                $assetPath = $this->getFallbackRessourceAssetPath($object, $asset, true);

                if ($reset) {
                    $this->filesystem->remove(Finder::create()->in($assetPath)->files());
                }

                $files[$name][$asset->getName()] = [];

                foreach($asset->getVariants() as $variant) {
                    if ($variant instanceof PictureVariant) {
                        $files[$name][$asset->getName()][$variant->getName()] = $this->generatePictureVariantResource($object, $variant);
                    }
                }
            }
        }

        return $files;
    }

    private function getFallbackRessourceAssetPath(UploadableInterface $entity, AbstractAsset $asset, bool $create = false)
    {
        $dir = $this->uploadableEntityBridge->getFallbackRessourceAssetPath($entity, $asset, false);

        if (!is_dir($dir) && $create) {
            $this->filesystem->mkdir($dir);
        }

        return $dir;
    }

    private function getFontPath(): string
    {
        return $this->appPublicDir.'/fonts/verdana.ttf';
    }

}
