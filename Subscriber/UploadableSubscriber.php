<?php

namespace Santeacademie\SuperUploaderBundle\Subscriber;

use Santeacademie\SuperUploaderBundle\Bridge\UploadableEntityBridge;
use Santeacademie\SuperUploaderBundle\Event\UploadableDeletedEvent;
use Santeacademie\SuperUploaderBundle\Event\UploadablePersistedEvent;
use Santeacademie\SuperUploaderBundle\Bridge\UploadablePersistentBridge;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableTemporaryBridge;
use Santeacademie\SuperUtil\LambdaUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UploadableSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private UploadableTemporaryBridge $uploadableTemporaryBridge,
        private UploadablePersistentBridge $uploadablePersistentBridge
    )
    {

    }

    public static function getSubscribedEvents(): array
    {
        return array_reduce([
            UploadablePersistedEvent::class,
            UploadableDeletedEvent::class,
        ], LambdaUtil::classnamesToEventsSubscriptions());
    }

    public function onUploadablePersistedEvent(UploadablePersistedEvent $event)
    {
        // Persist temporary files on the Pipe
        $files = $this->uploadablePersistentBridge->persistTemporaryVariantFiles($event->getUploadableEntity());

        // Remove temporary files from the Pipe
        $this->uploadableTemporaryBridge->removeIndexedEntityWithTemporaryVariants($event->getUploadableEntity());
    }

    public function onUploadableDeletedEvent(UploadableDeletedEvent $event)
    {
        // Remove persisted files on the Pipe
        $this->uploadablePersistentBridge->removeEntityVariantFiles($event->getUploadableEntity());

        // Flush deletable file Pipe
        $this->uploadablePersistentBridge->removeIndexedEntityWithDeletableVariants($event->getUploadableEntity());
    }

}