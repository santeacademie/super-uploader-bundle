<?php

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Santeacademie\SuperUploaderBundle\Manager\VariantEntityMapManagerInterface;
use Santeacademie\SuperUploaderBundle\Repository\VariantEntityMapRepository;
use Santeacademie\SuperUploaderBundle\Repository\VariantEntityMapRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('santeacademie.super_uploader.repository.variant_entity_map', VariantEntityMapRepository::class)
        ->args([
            service(VariantEntityMapManagerInterface::class),
        ])
        ->alias(VariantEntityMapRepositoryInterface::class, 'santeacademie.super_uploader.repository.variant_entity_map')
        ->alias(VariantEntityMapRepository::class, 'santeacademie.super_uploader.repository.variant_entity_map')
    ;
};


