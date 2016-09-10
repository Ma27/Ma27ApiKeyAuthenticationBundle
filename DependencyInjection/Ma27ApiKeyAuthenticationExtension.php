<?php

namespace Ma27\ApiKeyAuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class Ma27ApiKeyAuthenticationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('ma27_api_key_authentication.key_header', $config['key_header']);
        $container->setParameter('ma27_api_key_authentication.response_values', $config['response']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $this->loadCore($container, $loader, $config['user']);
        $this->loadServices($loader);

        if ($this->isConfigEnabled($container, $config['api_key_purge'])) {
            $this->loadApiKeyPurger($container, $loader, $config['api_key_purge']);
        }

        $services = array_filter($config['services']);
        if (!empty($services)) {
            $this->overrideServices($container, $services);
        }

        if ($config['user']['metadata_cache']) {
            $cacheDir = $container->getParameter('kernel.cache_dir');

            $container->getDefinition('ma27_api_key_authentication.class_metadata_factory')
                ->replaceArgument(3, sprintf('%s/ma27_api_key_authentication/metadata_dump', $cacheDir));
        }
    }

    /**
     * Loads the user and core related stuff of the bundle.
     *
     * @param ContainerBuilder      $container
     * @param Loader\YamlFileLoader $loader
     * @param array                 $config
     */
    private function loadCore(ContainerBuilder $container, Loader\YamlFileLoader $loader, $config)
    {
        $isCacheEnabled = $config['metadata_cache'];

        $container->setParameter('ma27_api_key_authentication.model_name', $config['model_name']);
        $container->setParameter('ma27_api_key_authentication.object_manager', $config['object_manager']);
        $container->setParameter('ma27_api_key_authentication.property.apiKeyLength', $config['api_key_length']);
        $container->setParameter('ma27_api_key_authentication.metadata_cache_enabled', $isCacheEnabled);

        if ($isCacheEnabled) {
            $loader->load('metadata_cache_warmer.yml');
        }

        $this->loadPassword($container, $config['password'], $loader);
    }

    /**
     * Loads the password strategy.
     *
     * @param ContainerBuilder      $container
     * @param string                $passwordConfig
     * @param Loader\YamlFileLoader $loader
     */
    private function loadPassword(ContainerBuilder $container, $passwordConfig, Loader\YamlFileLoader $loader)
    {
        $container->setParameter('ma27_api_key_authentication.password_hashing_service', $passwordConfig['strategy']);
        $container->setParameter('ma27_api_key_authentication.password_hasher.phpass.iteration_length', 8);
        $container->setParameter('ma27_api_key_authentication.password_hasher.php55.cost', 12);

        $loader->load('hashers.yml');
    }

    /**
     * Loads all internal services.
     *
     * @param Loader\YamlFileLoader $loader
     */
    private function loadServices(Loader\YamlFileLoader $loader)
    {
        foreach (array('security_key', 'authentication', 'security', 'annotation') as $file) {
            $loader->load(sprintf('%s.yml', $file));
        }
    }

    /**
     * Loads the purger job command into the container.
     *
     * @param ContainerBuilder      $container
     * @param Loader\YamlFileLoader $loader
     * @param string[]              $purgerConfig
     */
    private function loadApiKeyPurger(ContainerBuilder $container, Loader\YamlFileLoader $loader, array $purgerConfig)
    {
        $container->setParameter('ma27_api_key_authentication.cleanup_command.date_time_rule', $purgerConfig['outdated_rule']);
        $loader->load('session_cleanup.yml');

        if ($this->isConfigEnabled($container, $purgerConfig['last_action_listener'])) {
            $loader->load('last_action_listener.yml');
        }
    }

    /**
     * Processes the service override configuration into the container.
     *
     * @param ContainerBuilder $container
     * @param array            $services
     */
    private function overrideServices(ContainerBuilder $container, array $services)
    {
        $serviceConfig = array(
            'auth_handler' => 'ma27_api_key_authentication.auth_handler',
            'key_factory'  => 'ma27_api_key_authentication.key_factory',
        );

        foreach ($serviceConfig as $configIndex => $replaceableServiceId) {
            if (!isset($services[$configIndex]) || null === $serviceId = $services[$configIndex]) {
                continue;
            }

            $container->removeDefinition($replaceableServiceId);
            $container->setAlias($replaceableServiceId, new Alias($serviceId));
        }
    }
}
