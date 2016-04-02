<?php

namespace Ma27\ApiKeyAuthenticationBundle\DependencyInjection;

use Ma27\ApiKeyAuthenticationBundle\Security\ApiKeyAuthenticator;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ma27_api_key_authentication');

        $rootNode
            ->children()
                ->arrayNode('user')
                    ->children()
                        ->integerNode('api_key_length')
                            ->min(50)
                            ->defaultValue(200)
                        ->end()
                        ->scalarNode('object_manager')->isRequired()->end()
                        ->scalarNode('model_name')->defaultValue('AppBundle\\Entity\\User')->end()
                        ->arrayNode('password')
                            ->children()
                                ->scalarNode('strategy')
                                    ->validate()
                                        ->ifNotInArray(array('php55', 'crypt', 'sha512', 'phpass'))
                                        ->thenInvalid(
                                            'Invalid password strategy "%s"! '
                                            .'Allowed strategies are "password", "crypt", "sha512", "phpass"!'
                                        )
                                    ->end()
                                ->end()
                                ->integerNode('phpass_iteration_length')
                                    ->defaultValue(8)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('api_key_purge')
                    ->canBeEnabled()
                    ->children()
                        ->booleanNode('log_state')->defaultFalse()->end()
                        ->scalarNode('logger_service')->defaultValue('logger')->end()
                    ->end()
                ->end()
                ->arrayNode('services')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('auth_handler')->defaultNull()->end()
                        ->scalarNode('key_factory')->defaultNull()->end()
                        ->scalarNode('password_hasher')->defaultNull()->end()
                    ->end()
                ->end()
                ->scalarNode('key_header')->defaultValue(ApiKeyAuthenticator::API_KEY_HEADER)->end()
            ->end();

        return $treeBuilder;
    }
}
