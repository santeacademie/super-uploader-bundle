<?php

namespace Santeacademie\SuperUploaderBundle\Asset;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Asset\Variant\PictureVariant;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractAsset
{

    const PROPERTY_SCOPE_PUBLIC = 'public';
    const PROPERTY_SCOPE_PRIVATE = 'private';

    protected $propertyName;
    protected $propertyScope;
    protected $variants = [];

    public function __construct(protected ?string $name = null)
    {
        $childAsset = get_called_class();

        $this->variants = array_reduce($this->supportedVariants(), function(?array $carry, AbstractVariant $variant) use($childAsset) {
            if (isset($carry[$variant->getName()])) {
                throw new DuplicateKeyException(sprintf('Duplication name \'%s\' for Variant \'%s\' in Asset \'%s\'',
                    $variant->getName(),
                    get_class($variant),
                    $childAsset
                ));
            }

            $variant->setAsset($this);
            $carry[$variant->getName()] = $variant;

            return $carry;
        }, []);
    }

    public function getPropertyName()
    {
        return $this->propertyName;
    }

    public function setPropertyName(string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    public function getPropertyScope(): ?string
    {
        return $this->propertyScope;
    }

    public function setPropertyScope(string $propertyScope = null): self
    {
        $this->propertyScope = $propertyScope;

        return $this;
    }

    public function setName(?string $name = null): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    abstract public function getLabel(): string;

    abstract public function getMediaType(): string;

    abstract public function supportedVariants(): array;

    public function supportsVariantName(string $variantName)
    {
        return isset($this->variants[$variantName]);
    }

    public function supportsVariant(AbstractVariant $variant)
    {
        return $this->supportsVariantName($variant->getName());
    }

    public function getVariant(string $variantName): ?AbstractVariant
    {
        if (!isset($this->variants[$variantName])) {
            throw new \LogicException(sprintf('The variant "%s" doesn\'t exist for asset "%s"', $variantName, $this->getName()));
        }

        return $this->variants[$variantName];
    }

    public function getVariants(): array
    {
        return $this->variants;
    }

    public function getVariantFile(string $variantName, bool $fallbackResource = AbstractVariant::DEFAULT_FALLBACK_RESOURCE): ?File
    {
        return $this->getVariant($variantName)->getVariantFile($fallbackResource);
    }

    public function setVariantFile(string $variantName, File $variantFile): self
    {
        $this->getVariant($variantName)->setVariantFile($variantFile);

        return $this;
    }

    public function hasPropertyScopePublic()
    {
        return $this->propertyScope == self::PROPERTY_SCOPE_PUBLIC;
    }

    public function hasPropertyScopePrivate()
    {
        return $this->propertyScope == self::PROPERTY_SCOPE_PRIVATE;
    }

    public function getVariantFileUri(string $variantName, bool $fallbackResource = AbstractVariant::DEFAULT_FALLBACK_RESOURCE): ?string
    {
        $urlPackage = new UrlPackage(
            $_ENV['EXTERNAL_URL'],
            new EmptyVersionStrategy()
        );

        $variantFile = $this->getVariant($variantName)->getVariantFile($fallbackResource);

        if (!$fallbackResource && is_null($variantFile)) {
            return null;
        }

        if ($fallbackResource && is_null($variantFile)) {
            throw new \LogicException(sprintf('Variant file and fallback resource file are empty. There are 3 possibilities: %s',
                implode(PHP_EOL, [
                    "",
                    "- Fallback resources aren't generated then execute: php bin/console app:uploader:generate:resources",
                    "- Uploadable entity isn't populated with its asset then use: UploadableEntityBridge->populateUploadableFields",
                    "- You don't want to populate and you have to use: UploadableEntityBridge->getEntityAssetVariantFile"
                ])
            ));
        }

        return $urlPackage->getUrl($variantFile);
    }

}