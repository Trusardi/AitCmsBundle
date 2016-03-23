<?php

namespace Ait\CmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ait_cms');

        //todo: this is ugly, the class.page should be required if enable_routing is true
        $rootNode
            ->children()
                ->booleanNode('enable_routing')->defaultTrue()->end()
                ->scalarNode('first_page_route_action')->defaultNull()->end()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('parent_block')->defaultValue('Application\\Ait\\CmsBundle\\Entity\\ParentBlock')->end()
                        ->scalarNode('page')->defaultValue('Application\\Ait\\CmsBundle\\Entity\\Page')->end()
                        ->scalarNode('sonata_media')->defaultValue('Application\\Sonata\\MediaBundle\\Entity\\Media')->end()
                    ->end()
                ->end()
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('page')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->cannotBeEmpty()->defaultValue('Ait\\CmsBundle\\Admin\\PageAdmin')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('translation')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('locales')
                            ->requiresAtLeastOneElement()
                            ->prototype('scalar')->end()
                            ->defaultValue(['en'])
                        ->end()
                        ->scalarNode('default_locale')->defaultValue('en')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
