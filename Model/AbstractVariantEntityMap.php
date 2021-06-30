<?php

namespace Santeacademie\SuperUploaderBundle\Model;

class AbstractVariantEntityMap
{

    protected $fullPath;
    protected $assetName;
    protected $variantName;
    protected $mediaType;

    protected $assetClass;
    protected $variantClass;
    protected $variantTypeClass;
    protected $fileSize;
    protected $fileExtension;
    protected $pictureWidth;
    protected $pictureHeight;
    protected $createdAt;

    protected $entityClass;
    protected $entityIdentifier;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    /**
     * @param mixed $fullPath
     */
    public function setFullPath(string $fullPath): self
    {
        $this->fullPath = $fullPath;

        return $this;
    }

    public function getAssetName(): string
    {
        return $this->assetName;
    }

    public function setAssetName(string $assetName): self
    {
        $this->assetName = $assetName;

        return $this;
    }

    public function getVariantName(): string
    {
        return $this->variantName;
    }

    public function setVariantName(string $variantName): self
    {
        $this->variantName = $variantName;

        return $this;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): self
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function getAssetClass(): ?string
    {
        return $this->assetClass;
    }

    public function setAssetClass(?string $assetClass): self
    {
        $this->assetClass = $assetClass;

        return $this;
    }

    public function getVariantClass(): ?string
    {
        return $this->variantClass;
    }

    public function setVariantClass(?string $variantClass): self
    {
        $this->variantClass = $variantClass;

        return $this;
    }

    public function getVariantTypeClass(): ?string
    {
        return $this->variantTypeClass;
    }

    public function setVariantTypeClass(?string $variantTypeClass): self
    {
        $this->variantTypeClass = $variantTypeClass;

        return $this;
    }

    public function getFileSize(): ?float
    {
        return $this->fileSize;
    }

    public function setFileSize(float $fileSize): self
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getFileExtension(): ?string
    {
        return $this->fileExtension;
    }

    public function setFileExtension(?string $fileExtension): self
    {
        $this->fileExtension = $fileExtension;

        return $this;
    }

    public function getPictureWidth(): ?float
    {
        return $this->pictureWidth;
    }

    public function setPictureWidth(?float $pictureWidth): self
    {
        $this->pictureWidth = $pictureWidth;

        return $this;
    }

    public function getPictureHeight(): ?float
    {
        return $this->pictureHeight;
    }

    public function setPictureHeight(?float $pictureHeight): self
    {
        $this->pictureHeight = $pictureHeight;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    public function setEntityClass(?string $entityClass): self
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    public function getEntityIdentifier(): ?string
    {
        return $this->entityIdentifier;
    }

    public function setEntityIdentifier(?string $entityIdentifier): self
    {
        $this->entityIdentifier = $entityIdentifier;

        return $this;
    }



}
