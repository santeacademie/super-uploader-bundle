<?php

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Santeacademie\SuperUploaderBundle\Manager\VariantEntityMapManagerInterface;
use Santeacademie\SuperUploaderBundle\Repository\VariantEntityMapRepository;
use Santeacademie\SuperUploaderBundle\Repository\VariantEntityMapRepositoryInterface;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableTemporaryBridge;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableEntityBridge;
use Santeacademie\SuperUploaderBundle\Bridge\UploadablePersistentBridge;
use Santeacademie\SuperUploaderBundle\Command\FallbackResourcesGeneratorCommand;
use Santeacademie\SuperUploaderBundle\Command\GenerateDatabaseVariantMapCommand;
use Santeacademie\SuperUploaderBundle\Generator\FallbackResourcesGenerator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Santeacademie\SuperUploaderBundle\Form\AssetType\AssetType;
use Santeacademie\SuperUploaderBundle\Form\VariantType\ImagickCropVariantType;
use Santeacademie\SuperUploaderBundle\Transformer\ImagickCropTransformer;
use Santeacademie\SuperUploaderBundle\Transformer\FileTransformer;
use Santeacademie\SuperUploaderBundle\Twig\UploaderTwigExtension;
use Santeacademie\SuperUploaderBundle\Subscriber\UploadableSubscriber;
use Santeacademie\SuperUploaderBundle\EventListener\UploadableEntityListener;
use Santeacademie\SuperUploaderBundle\Interface\VariantTansformerInterface;


return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
            
        ->instanceof(VariantTansformerInterface::class)
            ->tag('super_uploader.transformer')
    
        ->set('super_uploader.repository.variant_entity_map', VariantEntityMapRepository::class)
        ->args([
            service(VariantEntityMapManagerInterface::class),
        ])
        ->alias(VariantEntityMapRepositoryInterface::class, 'super_uploader.repository.variant_entity_map')
        ->alias(VariantEntityMapRepository::class, 'super_uploader.repository.variant_entity_map')

            
        ->set('super_uploader.bridge.temporary', UploadableTemporaryBridge::class)
        ->args([
            '%kernel.project_dir%/public',
            '%super_uploader.mountpoint.temp%',
            service(Filesystem::class),
        ])
        ->alias(UploadableTemporaryBridge::class, 'super_uploader.bridge.temporary')
            
        ->set('super_uploader.bridge.persistent', UploadablePersistentBridge::class)
        ->args([
            '%kernel.project_dir%/public',
            '%super_uploader.mountpoint.uploads%',
            service(Filesystem::class),
            service(UploadableTemporaryBridge::class),
            service(VariantEntityMapRepository::class),
        ])
        ->alias(UploadablePersistentBridge::class, 'super_uploader.bridge.persistent')
            
        ->set('super_uploader.bridge.entity', UploadableEntityBridge::class)
        ->args([
            '%kernel.project_dir%/public',
            '%super_uploader.mountpoint.resources%',
            service(Filesystem::class),
            service(UploadablePersistentBridge::class),
            service(UploadableTemporaryBridge::class),
            service(EventDispatcherInterface::class),
        ])
        ->alias(UploadableEntityBridge::class, 'super_uploader.bridge.entity')
            
            
            
        ->set('super_uploader.generator.fallback_resources', FallbackResourcesGenerator::class)
            ->args([
                '%kernel.project_dir%/public',
                service(Filesystem::class),
                service(EntityManagerInterface::class),
                service(KernelInterface::class),
                service(UploadableEntityBridge::class),

            ])
        ->alias(FallbackResourcesGenerator::class, 'super_uploader.generator.fallback_resources')
            
                
            
        ->set('super_uploader.command.fallback_resources_generator', FallbackResourcesGeneratorCommand::class)
            ->args([
                service(FallbackResourcesGenerator::class)
            ])
            ->tag('console.command')
        ->alias(FallbackResourcesGeneratorCommand::class, 'super_uploader.command.fallback_resources_generator')
            
            
        ->set('super_uploader.command.generate_database_variant_map', GenerateDatabaseVariantMapCommand::class)
            ->args([
                '%kernel.project_dir%/public',
                service(EntityManagerInterface::class),
                service(UploadablePersistentBridge::class),
                service(UploadableEntityBridge::class),
            ])
            ->tag('console.command')
        ->alias(GenerateDatabaseVariantMapCommand::class, 'super_uploader.command.generate_database_variant_map')
            
            
        ->set('super_uploader.form.asset_type', AssetType::class)
            ->args([
                service(UploadableTemporaryBridge::class),
                service(UploadableEntityBridge::class),
                service(EventDispatcherInterface::class),
            ])
            ->tag('form.type')
        ->alias(AssetType::class, 'super_uploader.form.asset_type')
            
            
     

            
        ->set('super_uploader.transformer.imagick_crop', ImagickCropTransformer::class)
            ->alias(ImagickCropTransformer::class, 'super_uploader.transformer.imagick_crop')
            
        ->set('super_uploader.transformer.identity', FileTransformer::class)
            ->alias(FileTransformer::class, 'super_uploader.transformer.identity')

            
        ->set('super_uploader.form.variant_type.imagick_crop', ImagickCropVariantType::class)
            ->args([
                service(ImagickCropTransformer::class),
            ])
            ->tag('form.type')
        ->alias(ImagickCropVariantType::class, 'super_uploader.form.variant_type.imagick_crop')
            
            
            
            
        ->set('super_uploader.twig.uploader_extension', UploaderTwigExtension::class)
            ->args([
                service(UploadableEntityBridge::class),
            ])
            ->tag('twig.extension')
        ->alias(UploaderTwigExtension::class, 'super_uploader.twig.uploader_extension')
            
        ->set('super_uploader.subscriber.uploadable', UploadableSubscriber::class)
            ->args([
                service(UploadableTemporaryBridge::class),
                service(UploadablePersistentBridge::class),
            ])
            ->tag('kernel.event_subscriber')
        ->alias(UploadableSubscriber::class, 'super_uploader.subscriber.uploadable')
            
        ->set('super_uploader.doctrine.entity_listener', UploadableEntityListener::class)
            ->args([
                service(EventDispatcherInterface::class),
                service(UploadableTemporaryBridge::class),
                service(UploadablePersistentBridge::class),
                service(UploadableEntityBridge::class),
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'postLoad',
                'method' => 'postLoad'
            ])
            /*
            ->tag('doctrine.event_listener', [
                'event' => 'onFlush',
                'method' => 'onFlush',
                'priority' => -1
            ])
             */
            ->tag('doctrine.event_listener', [
                'event' => 'postFlush',
                'method' => 'postFlush',
                'priority' => 1
            ])
        ->alias(UploadableEntityListener::class, 'super_uploader.doctrine.entity_listener')
    ;
};
