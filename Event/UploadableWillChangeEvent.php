<?php

namespace Santeacademie\SuperUploaderBundle\Event;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Super\Interfaces\UploadableInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\EventDispatcher\Event;

class UploadableWillChangeEvent extends Event
{

    protected $oldValue;
    protected $newValue;
    protected $variant;
    protected $uploadableEntity;

    public function __construct(AbstractVariant $variant, UploadableInterface $uploadableEntity, ?File $oldValue, ?File $newValue)
    {
        $this->variant = $variant;
        $this->uploadableEntity = $uploadableEntity;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;

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