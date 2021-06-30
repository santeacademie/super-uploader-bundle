<?php

namespace Santeacademie\SuperUploaderBundle\Event;

use Santeacademie\SuperUploaderBundle\Super\Interfaces\UploadableInterface;
use Symfony\Contracts\EventDispatcher\Event;

class UploadablePersistedEvent extends Event
{
    /**
     * @var UploadableInterface $entity
     */
    protected $uploadableEntity;

    public function __construct(UploadableInterface $uploadableEntity)
    {
        $this->uploadableEntity = $uploadableEntity;
    }

    /**
     * @return UploadableInterface
     */
    public function getUploadableEntity(): UploadableInterface
    {
        return $this->uploadableEntity;
    }
}