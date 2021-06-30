<?php
/**
 * @author JRK <jessym@santeacademie.com>
 */

namespace Santeacademie\SuperUploaderBundle;

use Santeacademie\SuperUploaderBundle\DependencyInjection\CompilerPass\RegisterDoctrineOrmMappingPass;
use Santeacademie\SuperUploaderBundle\DependencyInjection\SuperUploaderExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SuperUploaderBundle extends Bundle
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $this->configureDoctrineExtension($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new SuperUploaderExtension();
    }

    private function configureDoctrineExtension(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterDoctrineOrmMappingPass());
    }
}
