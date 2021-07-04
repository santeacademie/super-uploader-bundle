<?php
/**
 * @author JRK <jessym@santeacademie.com>
 */

namespace Santeacademie\SuperUploaderBundle\DependencyInjection;

use Santeacademie\SuperUploaderBundle\Bridge\UploadablePersistentBridge;
use Santeacademie\SuperUploaderBundle\DependencyInjection\Configuration;
use Santeacademie\SuperUploaderBundle\Manager\Doctrine\VariantEntityMapManager;
use Santeacademie\SuperUploaderBundle\Persistence\Mapping\Driver;
use Santeacademie\SuperUploaderBundle\Repository\VariantEntityMapRepository;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Santeacademie\SuperUploaderBundle\Interface\VariantTansformerInterface;


class SuperUploaderExtension extends Extension implements CompilerPassInterface
{

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $config = $this->processConfiguration(new Configuration(), $configs);

        $this->configurePersistence($loader, $container, $config);

        foreach($config['mountpoints'] as $mntName => $mntValue) {
            $container->setParameter('super_uploader.mountpoint.'.$mntName, $mntValue);
        }
        
         $container->registerForAutoconfiguration(VariantTansformerInterface::class)
            ->addTag('super_uploader.transformer')
        ;
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
        $enabled = \count($config['persistence']) !== 0;

        $container->setParameter('super_uploader.persistence.enabled', $enabled);

        if (!$enabled) {
            return;
        }

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
            ->replaceArgument(1, $config['persistence']['doctrine']['table_name'])
            ->replaceArgument(2, $config['persistence']['doctrine']['schema_name'] ?? null)
        ;

        $container
            ->findDefinition(UploadablePersistentBridge::class)
            ->replaceArgument(4, $container->getDefinition('super_uploader.repository.variant_entity_map'))
        ;

        $container->setParameter('super_uploader.persistence.doctrine.enabled', true);
        $container->setParameter('super_uploader.persistence.doctrine.manager', $entityManagerName);
    }

    private function configureInMemoryPersistence(ContainerBuilder $container): void
    {
        $container->setParameter('super_uploader.persistence.in_memory.enabled', true);
    }

}
