<?php

declare(strict_types=1);

use Santeacademie\SuperUploaderBundle\Manager\AccessTokenManagerInterface;
use Santeacademie\SuperUploaderBundle\Manager\AuthorizationCodeManagerInterface;
use Santeacademie\SuperUploaderBundle\Manager\ClientManagerInterface;
use Santeacademie\SuperUploaderBundle\Manager\InMemory\AccessTokenManager;
use Santeacademie\SuperUploaderBundle\Manager\InMemory\AuthorizationCodeManager;
use Santeacademie\SuperUploaderBundle\Manager\InMemory\ClientManager;
use Santeacademie\SuperUploaderBundle\Manager\InMemory\RefreshTokenManager;
use Santeacademie\SuperUploaderBundle\Manager\RefreshTokenManagerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('league.oauth2_server.manager.in_memory.client', ClientManager::class)
        ->alias(ClientManagerInterface::class, 'league.oauth2_server.manager.in_memory.client')
        ->alias(ClientManager::class, 'league.oauth2_server.manager.in_memory.client')

        ->set('league.oauth2_server.manager.in_memory.access_token', AccessTokenManager::class)
            ->args([
                null,
            ])
        ->alias(AccessTokenManagerInterface::class, 'league.oauth2_server.manager.in_memory.access_token')
        ->alias(AccessTokenManager::class, 'league.oauth2_server.manager.in_memory.access_token')

        ->set('league.oauth2_server.manager.in_memory.refresh_token', RefreshTokenManager::class)
            ->args([
                null,
            ])
        ->alias(RefreshTokenManagerInterface::class, 'league.oauth2_server.manager.in_memory.refresh_token')
        ->alias(RefreshTokenManager::class, 'league.oauth2_server.manager.in_memory.refresh_token')

        ->set('league.oauth2_server.manager.in_memory.authorization_code', AuthorizationCodeManager::class)
            ->args([
                null,
            ])
        ->alias(AuthorizationCodeManagerInterface::class, 'league.oauth2_server.manager.in_memory.authorization_code')
        ->alias(AuthorizationCodeManager::class, 'league.oauth2_server.manager.in_memory.authorization_code')
    ;
};
