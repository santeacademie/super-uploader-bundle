<?php

namespace Santeacademie\SuperUploaderBundle\Tests\App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use League\FlysystemBundle\FlysystemBundle;
use Santeacademie\SuperUploaderBundle\SuperUploaderBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel implements CompilerPassInterface
{

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new SuperUploaderBundle(),
            new FlysystemBundle(),
        ];
    }


    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) use ($loader) {

            $container->prependExtensionConfig('framework', ['test' => true]);

            $container->loadFromExtension('doctrine', [
                'orm' => [
                    'default_entity_manager' => 'default',
                    'auto_generate_proxy_classes' => true,
                    'entity_managers' => [
                        'default' => [
                            'connection' => 'default',
                            'mappings' => [
                                'TestEntities' => [
                                    'is_bundle' => false,
                                    'type' => 'attribute',
                                    'dir' => '%kernel.project_dir%/src/Entity/',
                                    'prefix' => 'Santeacademie\SuperUploaderBundle\Tests\App\Entity',
                                    'alias' => 'app',
                                ]
                            ]
                        ]
                    ]
                ],
                'dbal' => [
                    'driver' => 'pdo_sqlite',
                    'path' => '%kernel.cache_dir%/test_database.sqlite',
                ],
            ]);

            $container->loadFromExtension('flysystem', [
                'storages' => [
                    'local.uploads' => [
                        'adapter' => 'local',
                        'public_url' => '/',
                        'options' => [
                            'directory' => '%kernel.project_dir%/public/uploads'
                        ]
                    ],
                    'local.temp' => [
                        'adapter' => 'local',
                        'public_url' => '/',
                        'options' => [
                            'directory' => '%kernel.project_dir%/public/tmp'
                        ]
                    ],
                    'local.resources' => [
                        'adapter' => 'local',
                        'public_url' => '/',
                        'options' => [
                            'directory' => '%kernel.project_dir%/public/resources'
                        ]
                    ],
                ],
            ]);


            $container->loadFromExtension('super_uploader', [
                'flysystem' => [
                    'uploads' => 'local.uploads',
                    'temp' => 'local.temp',
                    'resources' => 'local.resources',
                ],
                'persistence' => [
                    'in_memory' => '~',
                ]
            ]);

        });

    }

    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition('form.factory')->setPublic(true);
    }


    public function getProjectDir(): string
    {
        return 'tests/app';
    }

}
