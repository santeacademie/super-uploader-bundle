<?php

namespace Santeacademie\SuperUploaderBundle\Interface;

use App\Core\Super\Entity\GuessableEntityIdentifierInterface;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Symfony\Component\HttpFoundation\File\File;

interface UploadableInterface extends GuessableEntityIdentifierInterface
{
    /**
     * @return array|AbstractAsset[]
     */
    public function getLoadUploadableAssets(): array;

    public function getUploadableAssetByName(string $assetName): AbstractAsset;

    public function getUploadEntityPath(): string;

    public function getUploadEntityToken(): ?string;
}