<?php

namespace Santeacademie\SuperUploaderBundle\Super\Interfaces;

use App\Core\Super\Entity\GuessableEntityIdentifierInterface;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Symfony\Component\HttpFoundation\File\File;

interface UploadableInterface extends GuessableEntityIdentifierInterface
{
    public function getLoadUploadableAssets(): array;

    public function getUploadableAssetByName(string $assetName): AbstractAsset;

    public function getUploadEntityPath(): string;

    public function getUploadEntityToken(): ?string;
}