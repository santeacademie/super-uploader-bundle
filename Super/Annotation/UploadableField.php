<?php

namespace Santeacademie\SuperUploaderBundle\Super\Annotation;

use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Symfony\Component\Routing\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Annotation class for SelectOption
 *
 * @Annotation
 * @Target({"ALL"})
 *
 */
class UploadableField
{

    private $name;
    private $asset;

    /**
     * @param array $data An array of key/value parameters
     *
     * @throws \BadMethodCallException
     */
    public function __construct(array $data)
    {

        if (!isset($data['asset'])) {
            throw new \BadMethodCallException(sprintf('Unknown property "asset" on annotation "%s".', static::class));
        }

        $assetClass = $data['asset'];
        $assetName = $data['name'] ?? null;

        if (!class_exists($assetClass)) {
            throw new \UnexpectedValueException(sprintf('Asset Class "%s" doesn\'t exist.', $assetClass));
        }

        $parents = array_values(class_parents($assetClass));

        if (empty($parents) || $parents[0] !== AbstractAsset::class) {
            throw new \UnexpectedValueException(sprintf('Class "%s" isn\'t a valid %s class for an %s annotation.', $assetClass, AbstractAsset::class, static::class));
        }

        $this
            ->setAsset(new $assetClass())
            ->setName($assetName)
        ;

        unset($data['asset']);
        unset($data['name']);

        if (!empty($data)) {
            foreach($data as $attributeName => $attributeValue) {
                throw new \UnexpectedValueException(sprintf('Attribute "%s" doesn\'t exist for annotation class "%s".', $attributeName, static::class));
            }
        }
    }

    public function getAsset(): AbstractAsset
    {
        return $this->asset;
    }

    public function setAsset(AbstractAsset $asset): self
    {
        $this->asset = $asset;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name = null): self
    {
        $this->name = $name;

        return $this;
    }




}