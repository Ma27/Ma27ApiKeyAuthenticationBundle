<?php

namespace Ma27\ApiKeyAuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

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
                        ->scalarNode('model_name')->defaultValue('AppBundle:User')->end()
                        ->arrayNode('properties')
                            ->validate()
                                ->always(function ($properties) {
                                    $fieldValues = array(
                                        $properties['username'] ?: '',
                                        $properties['email'] ?: '',
                                    );

                                    if (empty($fieldValues[0]) && empty($fieldValues[1])) {
                                        throw new InvalidConfigurationException('Email and username cannot be null!');
                                    }

                                    if (count(array_unique($fieldValues)) < 2) {
                                        $valueCount = array_filter(
                                            array_count_values($fieldValues),
                                            function ($count) {
                                                return $count > 1;
                                            }
                                        );

                                        throw new InvalidConfigurationException(
                                            sprintf(
                                                'The user model properties must be unique! Duplicated items found: %s',
                                                implode(', ', array_keys($valueCount))
                                            )
                                        );
                                    }

                                    return $properties;
                                })
                            ->end()
                            ->children()
                                ->scalarNode('username')->defaultValue('username')->end()
                                ->scalarNode('email')->defaultNull()->end()
                                ->scalarNode('apiKey')
                                    ->defaultValue('apiKey')
                                    ->cannotBeEmpty()
                                ->end()
                                ->arrayNode('password')
                                    ->children()
                                        ->scalarNode('strategy')
                                            ->defaultValue('php55')
                                            ->validate()
                                            ->ifNotInArray(array('php55', 'crypt', 'sha512', 'phpass'))
                                                ->thenInvalid(
                                                    'Invalid password strategy "%s"! '
                                                    .'Allowed strategies are "password", "crypt", "sha512", "phpass"!'
                                                )
                                            ->end()
                                        ->end()
                                        ->scalarNode('property')
                                            ->defaultValue('password')
                                        ->end()
                                        ->integerNode('phpass_iteration_length')
                                            ->defaultValue(8)
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('api_key_purge')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('last_active_property')->end()
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
            ->end();

        return $treeBuilder;
    }
}
