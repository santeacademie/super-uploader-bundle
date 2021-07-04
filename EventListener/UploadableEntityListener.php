<?php

namespace Santeacademie\SuperUploaderBundle\EventListener;

//use App\Core\Lifecycle\ListenableEntityCollector;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableEntityBridge;
use Santeacademie\SuperUploaderBundle\Super\Interfaces\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Event\UploadableDeletedEvent;
use Santeacademie\SuperUploaderBundle\Event\UploadablePersistedEvent;
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
        private UploadableEntityBridge $uploadableEntityBridge,
//        private ListenableEntityCollector $listenableEntityCollector
    )
    {

    }

//    public function onFlush(OnFlushEventArgs $args): void
//    {
//        foreach ($this->listenableEntityCollector->getCollectedInsertions() as $entity) {
//            if ($entity instanceof UploadableInterface) {
//                $this->uploadableEntityBridge->populateUploadableFields($entity);
//            }
//        }
//
//        foreach ($this->listenableEntityCollector->getCollectedUpdates() as $entity) {
//            if ($entity instanceof UploadableInterface) {
//                $this->uploadableEntityBridge->populateUploadableFields($entity);
//            }
//        }
//
//        foreach($this->listenableEntityCollector->getCollectedDeletions() as $entity) {
//            if ($entity instanceof UploadableInterface) {
//                $this->uploadablePersistentBridge->indexEntityVariantsToDelete($entity);
//            }
//        }
//    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        foreach ($this->uploadableTemporaryBridge->getIndexedEntitiesWithTemporaryVariants() as $entity) {
            if ($entity instanceof UploadableInterface) {
                $this->eventDispatcher->dispatch(new UploadablePersistedEvent($entity));
            }
        }

        foreach ($this->uploadablePersistentBridge->getIndexedEntitiesWithDeletableVariants() as $entity) {
            if ($entity instanceof UploadableInterface) {
                $this->eventDispatcher->dispatch(new UploadableDeletedEvent($entity));
            }
        }
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof UploadableInterface) {
//            $this->uploadableEntityBridge->populateUploadableFields($entity);
        }
    }

}
