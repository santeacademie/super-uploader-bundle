<?php

namespace Santeacademie\SuperUploaderBundle\Event;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractVariantUpdateRequestEvent extends Event
{

    public function __construct(
        protected AbstractVariant $variant,
        protected  UploadableInterface $uploadableEntity,
        protected ?File $oldValue,
        protected ?File $newValue
    )
    {

    }

    public function getVariant(): AbstractVariant
    {
        return $this->variant;
    }

    public function getUploadableEntity(): UploadableInterface
    {
        return $this->uploadableEntity;
    }

    public function getOldValue(): ?File
    {
        return $this->oldValue;
    }

    public function setOldValue(?File $oldValue): self
    {
        $this->oldValue = $oldValue;

        return $this;
    }

    public function getNewValue(): ?File
    {
        return $this->newValue;
    }

    public function setNewValue(?File $newValue): self
    {
        $this->newValue = $newValue;

        return $this;
    }



}