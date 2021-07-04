<?php

namespace Santeacademie\SuperUploaderBundle\Event;

use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Symfony\Contracts\EventDispatcher\Event;

class UploadableDeletedEvent extends Event
{
    /**
     * @var UploadableInterface $uploadableEntity
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
