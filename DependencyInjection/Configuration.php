<?php

namespace TransBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('trans');

        $rootNode
                ->children()
                    ->scalarNode('layout')
                        ->cannotBeEmpty()
                        ->defaultValue('TransBundle::layout.html.twig')
                    ->end()
                    ->scalarNode('items_per_page')
                        ->cannotBeEmpty()
                        ->defaultValue(25)
                    ->end()
                    ->arrayNode('locales')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->prototype('scalar')
                    ->end()
                ->end()
        ;
        
        return $treeBuilder;
    }
}
