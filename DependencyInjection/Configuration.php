<?php
/**
 * @author JRK <jessym@santeacademie.com>
 */

namespace Santeacademie\SuperUploaderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('super_uploader');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC for symfony/config < 4.2
            $rootNode = $treeBuilder->root('super_uploader');
        }

        $rootNode
            ->children()
                ->append($this->getTwigGlobalsEnabled())
                ->end()
            ;

        return $treeBuilder;
    }

    private function getTwigGlobalsEnabled(): ScalarNodeDefinition
    {
        $node = new BooleanNodeDefinition('twig_globals_enabled');
        $node->defaultValue(true);

        return $node;
    }

}
