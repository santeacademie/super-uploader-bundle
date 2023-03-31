<?php
/**
 * @author JRK <jessym@santeacademie.com>
 */

namespace Santeacademie\SuperUploaderBundle\DependencyInjection;

use Santeacademie\SuperUploaderBundle\Model\AbstractVariantEntityMap;
use Santeacademie\SuperUploaderBundle\Model\VariantEntityMap;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('super_uploader');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->append($this->createFlystemNode())
            ->append($this->createPersistenceNode())
            ->append($this->createVariantEntityMapNode())
            ->end()
        ;

        return $treeBuilder;
    }

    private function createFlystemNode() : ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('flysystem');
        $node = $treeBuilder->getRootNode();

        $node
            ->isRequired()
            ->performNoDeepMerging()
                ->children()
                    ->scalarNode('uploads')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('temp')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('resources')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end()
            ;
        return $node;
    }

    private function createPersistenceNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('persistence');
        $node = $treeBuilder->getRootNode();

        $node
            ->info("Configures different persistence methods that can be used by the bundle for saving variant_entity_map.\nOnly one persistence method can be configured at a time.")
            ->isRequired()
            ->performNoDeepMerging()
            ->children()
                // Doctrine persistence
                ->arrayNode('doctrine')
                    ->children()
                        ->scalarNode('entity_manager')
                            ->info('Name of the entity manager that you wish to use for managing variant_entity_maps.')
                            ->cannotBeEmpty()
                            ->defaultValue('default')
                        ->end()
                
                        ->scalarNode('table_name')
                            ->cannotBeEmpty()
                            ->defaultValue('super_uploader_variant_entity_map')
                        ->end()
                
                        ->scalarNode('schema_name')
                            ->defaultValue(null)
                        ->end()
                    ->end()
                ->end()
                // In-memory persistence
                ->scalarNode('in_memory')
                ->end()
            ->end()
        ;

        return $node;
    }


    private function createVariantEntityMapNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('variant_entity_map');
        $node = $treeBuilder->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('classname')
            ->info(sprintf('Set a custom variant_entity_map class. Must be a %s', AbstractVariantEntityMap::class))
            ->defaultValue(VariantEntityMap::class)
            ->validate()
            ->ifTrue(function ($v) {
                return !is_a($v, AbstractVariantEntityMap::class, true);
            })
            ->thenInvalid(sprintf('%%s must be a %s', AbstractVariantEntityMap::class))
            ->end()
            ->end()
            ->end();

        return $node;
    }

}
