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
        $container->setParameter('ma27_api_key_authentication.model_name', $config['user']['model_name']);
        $container->setParameter('ma27_api_key_authentication.object_manager', $config['user']['object_manager']);
        $container->setParameter('ma27_api_key_authentication.property.apiKeyLength', $config['user']['api_key_length']);
        $container->setParameter('ma27_api_key_authentication.response_values', $config['response']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $this->loadPassword($container, $config['user']['password'], $loader);
        $this->loadServices($loader);

        if ($this->isConfigEnabled($container, $config['api_key_purge'])) {
            $this->loadApiKeyPurger($container, $loader, $config['api_key_purge']);
        }

        $services = array_filter($config['services']);
        if (!empty($services)) {
            $this->overrideServices($container, $services);
        }
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
