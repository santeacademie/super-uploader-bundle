<?php
/**
 * @author JRK <jessym@santeacademie.com>
 */

namespace Santeacademie\SuperUploaderBundle\DependencyInjection;

use Santeacademie\SuperUploaderBundle\DependencyInjection\Configuration;
use Santeacademie\SuperUploaderBundle\Manager\Doctrine\VariantEntityMapManager;
use Santeacademie\SuperUploaderBundle\Persistence\Mapping\Driver;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class SuperUploaderExtension extends Extension implements CompilerPassInterface
{

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $config = $this->processConfiguration(new Configuration(), $configs);
        
        $container->setParameter('super_uploader.twig_globals_enabled', $config['twig_globals_enabled'] ?? true);


        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $this->configurePersistence($loader, $container, $config);
    }

    public function process(ContainerBuilder $container)
    {
        $this->assertRequiredBundlesAreEnabled($container);
    }

    private function assertRequiredBundlesAreEnabled(ContainerBuilder $container): void
    {
        $requiredBundles = [
            'doctrine' => DoctrineBundle::class
        ];

        foreach ($requiredBundles as $bundleAlias => $requiredBundle) {
            if (!$container->hasExtension($bundleAlias)) {
                throw new \LogicException(sprintf('Bundle \'%s\' needs to be enabled in your application kernel.', $requiredBundle));
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function configurePersistence(LoaderInterface $loader, ContainerBuilder $container, array $config): void
    {
        if (\count($config['persistence']) > 1) {
            throw new \LogicException('Only one persistence method can be configured at a time.');
        }

        $persistenceConfig = current($config['persistence']);
        $persistenceMethod = key($config['persistence']);

        switch ($persistenceMethod) {
            case 'in_memory':
                $loader->load('storage/in_memory.php');
                $this->configureInMemoryPersistence($container);
                break;
            case 'doctrine':
                $loader->load('storage/doctrine.php');
                $this->configureDoctrinePersistence($container, $config, $persistenceConfig);
                break;
        }
    }


    private function configureDoctrinePersistence(ContainerBuilder $container, array $config, array $persistenceConfig): void
    {
        $entityManagerName = $persistenceConfig['entity_manager'];

        $entityManager = new Reference(
            sprintf('doctrine.orm.%s_entity_manager', $entityManagerName)
        );

        $container
            ->findDefinition(VariantEntityMapManager::class)
            ->replaceArgument(0, $entityManager)
            ->replaceArgument(1, $config['variant_entity_map']['classname'])
        ;
        
        $container
            ->findDefinition(Driver::class)
            ->replaceArgument(0, Client::class !== $config['variant_entity_map']['classname'])
        ;

        $container->setParameter('santeacademie.super_uploader.persistence.doctrine.enabled', true);
        $container->setParameter('santeacademie.super_uploader.persistence.doctrine.manager', $entityManagerName);
    }

    private function configureInMemoryPersistence(ContainerBuilder $container): void
    {
        $container->setParameter('santeacademie.super_uploader.persistence.in_memory.enabled', true);
    }

}
