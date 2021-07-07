<?php

namespace Santeacademie\SuperUploaderBundle\Event;

use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Symfony\Contracts\EventDispatcher\Event;

class UploadableEntityDeletedEvent extends Event
{

    public function __construct(
        private UploadableInterface $uploadableEntity
    )
    {

    }

    public function getUploadableEntity(): UploadableInterface
    {
        return $this->uploadableEntity;
    }




}