<?php

namespace Ajgl\Bundle\CpmBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ajgl_cpm');

        $rootNode
            ->children()
                ->scalarNode('install_dir')
                    ->defaultValue('%kernel.cache_dir%/cpm')
                ->end()
                ->scalarNode('target_dir')
                    ->defaultValue('%kernel.root_dir%/../web/cpm')
                ->end()
                ->arrayNode('packages')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('version')->end()
                        ->scalarNode('registry')->defaultValue('http://packages.dojofoundation.org/')->end()
                    ->end()
                ->end()
            ->end()
        ;
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
