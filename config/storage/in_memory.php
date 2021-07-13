<?php

use Santeacademie\SuperUploaderBundle\Manager\VariantEntityMapManagerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Santeacademie\SuperUploaderBundle\Manager\InMemory\VariantEntityMapManager;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('super_uploader.manager.in_memory.variant_entity_map', VariantEntityMapManager::class)
        ->alias(VariantEntityMapManagerInterface::class, 'super_uploader.manager.in_memory.variant_entity_map')
        ->alias(VariantEntityMapManager::class, 'super_uploader.manager.in_memory.variant_entity_map')



    ;
};

