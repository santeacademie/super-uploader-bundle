<?php

namespace Santeacademie\SuperUploaderBundle\Annotation;

use Symfony\Component\Routing\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Annotation class for UploadableTrait
 *
 * @Annotation
 * @Target({"ALL"})
 *
 */
class UploadableKey
{

    const DEFAULT_SPLIT_LENGTH = 1;

    private $splitLength;

    /**
     * @param array $data An array of key/value parameters
     *
     * @throws \BadMethodCallException
     */
    public function __construct(array $data)
    {
        $splitLength = $data['splitLength'] ?? self::DEFAULT_SPLIT_LENGTH;

        if ($splitLength <= 0) {
            throw new \LogicException(sprintf('UploadKey splitLength must be a positive value'));
        }

        $this
            ->setSplitLength($splitLength)
        ;

        unset($data['splitLength']);

        if (!empty($data)) {
            foreach($data as $attributeSplitLength => $attributeValue) {
                throw new \UnexpectedValueException(sprintf('Attribute "%s" doesn\'t exist for annotation class "%s".', $attributeSplitLength, static::class));
            }
        }
    }

    public function getSplitLength(): int
    {
        return $this->splitLength;
    }

    public function setSplitLength(?int $splitLength = null): self
    {
        $this->splitLength = $splitLength ?? self::DEFAULT_SPLIT_LENGTH;

        return $this;
    }

}