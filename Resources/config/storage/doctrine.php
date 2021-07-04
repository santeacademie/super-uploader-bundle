<?php

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Santeacademie\SuperUploaderBundle\Persistence\Mapping\Driver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Santeacademie\SuperUploaderBundle\Manager\VariantEntityMapManagerInterface;
use Santeacademie\SuperUploaderBundle\Manager\Doctrine\VariantEntityMapManager;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('super_uploader.persistence.driver', Driver::class)
            ->args([
                null,
                null,
                null,
            ])
        ->alias(Driver::class, 'super_uploader.persistence.driver')

        ->set('super_uploader.manager.doctrine.variant_entity_map', VariantEntityMapManager::class)
            ->args([
                null,
                null,
            ])
        ->alias(VariantEntityMapManagerInterface::class, 'super_uploader.manager.doctrine.variant_entity_map')
        ->alias(VariantEntityMapManager::class, 'super_uploader.manager.doctrine.variant_entity_map')

        
    ;
};
