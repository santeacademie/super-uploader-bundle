<?php

namespace Santeacademie\SuperUploaderBundle\Interface;

use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;

interface UploadableInterface
{
    /**
     * @return array|AbstractAsset[]
     */
    public function getLoadUploadableAssets(): array;

    public function getUploadableAssetByName(string $assetName): AbstractAsset;

    public function getUploadEntityPath(): string;

    public function getUploadEntityToken(): ?string;

    public function getUploadableKeyName(): string;

    public function getUploadableKeyValue(): string;
}