<?php
/**
 * @author JRK <jessym@santeacademie.com>
 */

namespace Santeacademie\SuperUploaderBundle\DependencyInjection\CompilerPass;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Santeacademie\SuperUploaderBundle\Persistence\Mapping\Driver;
use Symfony\Component\DependencyInjection\Reference;

class RegisterDoctrineOrmMappingPass extends DoctrineOrmMappingsPass
{
    public function __construct()
    {
        parent::__construct(
            driver: new Reference(Driver::class),
            namespaces: ['Santeacademie\SuperUploaderBundle\Model'],
            managerParameters: ['super_uploader.persistence.doctrine.manager'],
            enabledParameter: 'super_uploader.persistence.doctrine.enabled',
            aliasMap: ['SuperUploaderBundle' => 'Santeacademie\SuperUploaderBundle\Model']
        );
    }
}