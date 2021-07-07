<?php

namespace Santeacademie\SuperUploaderBundle\Subscriber;

use Santeacademie\SuperUploaderBundle\Event\PersistentVariantPreCreateEvent;
use Santeacademie\SuperUploaderBundle\Event\PersistentVariantPreDeleteEvent;
use Santeacademie\SuperUploaderBundle\Bridge\UploadablePersistentBridge;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableTemporaryBridge;
use Santeacademie\SuperUploaderBundle\Event\UploadableEntityDeletedEvent;
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
            PersistentVariantPreCreateEvent::class,
            PersistentVariantPreDeleteEvent::class,
            UploadableEntityDeletedEvent::class
        ], LambdaUtil::classnamesToEventsSubscriptions());
    }

    public function onPersistentVariantPreCreateEvent(PersistentVariantPreCreateEvent $event)
    {
        // Persist temporary files on the Pipe
        $this->uploadablePersistentBridge->persistTemporaryVariantFile($event->getVariant(), $event->getUploadableEntity());

        // Remove temporary files from the Pipe
        $this->uploadableTemporaryBridge->removeVariantByEntityFromIndex($event->getVariant(), $event->getUploadableEntity());
    }

    public function onPersistentVariantPreDeleteEvent(PersistentVariantPreDeleteEvent $event)
    {
        // Remove persisted files on the Pipe
        $this->uploadablePersistentBridge->removeEntityVariantFile($event->getVariant(), $event->getUploadableEntity());
    }

    public function onUploadableEntityDeletedEvent(UploadableEntityDeletedEvent $event)
    {
        $this->uploadablePersistentBridge->removeIndexedEntityWithDeletableVariants($event->getUploadableEntity());
    }

}