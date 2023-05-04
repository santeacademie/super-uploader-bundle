<?php

namespace Santeacademie\SuperUploaderBundle\Tests\App\Asset;

use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Asset\Variant\PictureVariant;
use Santeacademie\SuperUploaderBundle\Form\VariantType\ImagickCropVariantType;
use Santeacademie\SuperUploaderBundle\Select\SelectUploadMediaType;

class TestAsset extends AbstractAsset
{

    const VARIANT_PORTRAIT = 'portrait';
    const VARIANT_LANDSCAPE = 'landscape';

    public function getLabel(): string
    {
        return 'Profile picture';
    }

    public function supportedVariants(): array
    {
        return [
            new PictureVariant(
                variantTypeClass: ImagickCropVariantType::class,
                required:false,
                name: self::VARIANT_PORTRAIT,
                label: 'Portrait',
                width: 595,
                height: 895,
                extension: 'png'
            ),
            new PictureVariant(
                variantTypeClass: ImagickCropVariantType::class,
                required: false,
                name: self::VARIANT_LANDSCAPE,
                label: 'Landscape',
                width: 365,
                height: 298,
                extension: 'png'
            ),
        ];
    }

    public function getMediaType(): string
    {
        return SelectUploadMediaType::$PICTURE;
    }

}
