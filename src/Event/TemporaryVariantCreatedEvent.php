<?php

namespace Santeacademie\SuperUploaderBundle\Event;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Symfony\Contracts\EventDispatcher\Event;

class TemporaryVariantCreatedEvent extends Event
{

    public function __construct(
        private AbstractVariant $variant,
        private UploadableInterface $uploadableEntity
    )
    {

    }

    public function getUploadableEntity(): UploadableInterface
    {
        return $this->uploadableEntity;
    }

    public function getVariant(): AbstractVariant
    {
        return $this->variant;
    }


}