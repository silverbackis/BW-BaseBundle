<?php
namespace BW\BaseBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('bw_base');
        $rootNode
            ->children()
                ->arrayNode('page')
                    ->children()
                        ->arrayNode('metas')
                            ->normalizeKeys(false)
                            ->useAttributeAsKey('element')
                            
                            ->prototype('array')
                                ->normalizeKeys(false)
                                ->prototype('array')
                                    ->beforeNormalization()
                                    ->ifString()
                                        ->then(function($value) { return array('{{ master_key }}' => '{{ key }}', 'content' => $value); })
                                    ->end()
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()

                        ->end()
                        ->arrayNode('links')
                            ->normalizeKeys(false)
                            ->prototype('array')
                                ->normalizeKeys(false)
                                ->useAttributeAsKey('rel')
                                ->prototype('array')
                                    ->normalizeKeys(false)
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('type')->end()
                                            ->scalarNode('sizes')->end()
                                            ->scalarNode('href')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('js_sdk')
                            ->normalizeKeys(false)
                            ->children()
                                ->arrayNode('google_analytics')
                                    ->normalizeKeys(false)
                                    ->canBeEnabled()
                                    ->children()
                                        ->booleanNode('head')
                                            ->defaultTrue()
                                        ->end()
                                        ->scalarNode('id')->end()
                                        ->scalarNode('domain')
                                            ->defaultValue('auto')
                                        ->end()
                                        ->booleanNode('debug')
                                            ->defaultFalse()
                                        ->end()
                                    ->end()
                                ->end()

                                ->arrayNode('woopra')
                                    ->normalizeKeys(false)
                                    ->canBeEnabled()
                                    ->children()
                                        ->booleanNode('head')
                                            ->defaultTrue()
                                        ->end()
                                        ->scalarNode('domain')->end()
                                    ->end()
                                ->end()

                                ->arrayNode('facebook_pixel')
                                    ->normalizeKeys(false)
                                    ->canBeEnabled()
                                    ->children()
                                        ->booleanNode('head')
                                            ->defaultFalse()
                                        ->end()
                                        ->scalarNode('id')->end()
                                    ->end()
                                ->end()

                                ->arrayNode('facebook')
                                    ->normalizeKeys(false)
                                    ->canBeEnabled()
                                    ->children()
                                        ->booleanNode('head')
                                            ->defaultFalse()
                                        ->end()
                                        ->scalarNode('app_id')->end()
                                        ->scalarNode('admins')->end()
                                        ->booleanNode('xfbml')
                                            ->defaultTrue()
                                        ->end()
                                        ->scalarNode('version')
                                            ->defaultValue('v2.8')
                                        ->end()
                                        ->scalarNode('language')
                                            ->defaultValue('en_US')
                                        ->end()
                                        ->booleanNode('login_status_check')
                                            ->defaultFalse()
                                        ->end()
                                        ->booleanNode('debug')
                                            ->defaultFalse()
                                        ->end()
                                    ->end()
                                ->end()

                                ->arrayNode('twitter')
                                    ->normalizeKeys(false)
                                    ->canBeEnabled()
                                    ->children()
                                        ->booleanNode('head')
                                            ->defaultFalse()
                                        ->end()
                                    ->end()
                                ->end()

                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
