<?php

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Santeacademie\SuperUploaderBundle\Persistence\Mapping\Driver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Santeacademie\SuperUploaderBundle\Manager\VariantEntityMapManagerInterface;
use Santeacademie\SuperUploaderBundle\Manager\Doctrine\VariantEntityMapManager;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('santeacademie.super_uploader.persistence.driver', Driver::class)
            ->args([
                null,
            ])
        ->alias(Driver::class, 'santeacademie.super_uploader.persistence.driver')

        ->set('santeacademie.super_uploader.manager.doctrine.variant_entity_map', VariantEntityMapManager::class)
            ->args([
                null,
                null,
            ])
        ->alias(VariantEntityMapManagerInterface::class, 'santeacademie.super_uploader.manager.doctrine.variant_entity_map')
        ->alias(VariantEntityMapManager::class, 'santeacademie.super_uploader.manager.doctrine.variant_entity_map')

        
    ;
};
