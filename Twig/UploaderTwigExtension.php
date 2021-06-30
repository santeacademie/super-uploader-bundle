<?php

namespace Santeacademie\SuperUploaderBundle\Twig;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableEntityBridge;
use Santeacademie\SuperUploaderBundle\Super\Interfaces\UploadableInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UploaderTwigExtension extends AbstractExtension
{


    public function __construct(
        private UploadableEntityBridge $uploadableEntityBridge
    )
    {

    }

    public function getFilters()
    {
        return [
            new TwigFilter('uploadable', [$this, 'getUploadable']),
        ];
    }

    public function getUploadable(UploadableInterface $entity, string $assetName, string $variantName, bool $fallbackResource = AbstractVariant::DEFAULT_FALLBACK_RESOURCE)
    {
        $file = $this->uploadableEntityBridge->getNamedEntityAssetVariantFile($entity, $assetName, $variantName, $fallbackResource);

        return !is_null($file) ? $file->getPathname() : '';
    }
}