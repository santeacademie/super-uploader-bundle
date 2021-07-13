<?php

namespace Santeacademie\SuperUploaderBundle\EventListener;

use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableEntityBridge;
use Santeacademie\SuperUploaderBundle\Event\PersistentVariantPreCreateEvent;
use Santeacademie\SuperUploaderBundle\Event\PersistentVariantPreDeleteEvent;
use Santeacademie\SuperUploaderBundle\Event\UploadableEntityDeletedEvent;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Bridge\UploadablePersistentBridge;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableTemporaryBridge;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UploadableEntityListener
{

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private UploadableTemporaryBridge $uploadableTemporaryBridge,
        private UploadablePersistentBridge $uploadablePersistentBridge,
        private UploadableEntityBridge $uploadableEntityBridge
    )
    {

    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        foreach ($this->uploadableTemporaryBridge->getIndexedEntitiesWithTemporaryVariants() as $entity) {
            if ($entity instanceof UploadableInterface) {
                foreach($this->uploadableTemporaryBridge->getIndexedEntityTemporaryVariants($entity) as $variant) {
                    $this->eventDispatcher->dispatch(new PersistentVariantPreCreateEvent($variant, $entity));
                }
            }
        }

        foreach ($this->uploadablePersistentBridge->getIndexedEntitiesWithDeletableVariants() as $entity) {
            if ($entity instanceof UploadableInterface) {
                foreach ($entity->getLoadUploadableAssets() as $asset) {
                    foreach ($asset->getVariants() as $variant) {
                        $this->eventDispatcher->dispatch(new PersistentVariantPreDeleteEvent($variant, $entity));
                    }
                }

                $this->eventDispatcher->dispatch(new UploadableEntityDeletedEvent($entity));
            }
        }
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof UploadableInterface) {
            $this->uploadableEntityBridge->populateUploadableFields($entity);
        }
    }

}
