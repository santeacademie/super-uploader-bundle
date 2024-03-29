<?php

namespace Santeacademie\SuperUploaderBundle\Event;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Wrapper\SuperFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractVariantUpdateRequestEvent extends Event
{

    public function __construct(
        protected AbstractVariant $variant,
        protected  UploadableInterface $uploadableEntity,
        protected ?SuperFile $oldValue,
        protected ?SuperFile $newValue
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

    public function getOldValue(): ?SuperFile
    {
        return $this->oldValue;
    }

    public function setOldValue(?SuperFile $oldValue): self
    {
        $this->oldValue = $oldValue;

        return $this;
    }

    public function getNewValue(): ?SuperFile
    {
        return $this->newValue;
    }

    public function setNewValue(?SuperFile $newValue): self
    {
        $this->newValue = $newValue;

        return $this;
    }



}