<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\DependencyInjection;

use Ma27\ApiKeyAuthenticationBundle\DependencyInjection\Configuration;
use Ma27\ApiKeyAuthenticationBundle\Security\ApiKeyAuthenticator;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "ma27_api_key_authentication.user.password.strategy": Invalid password strategy ""md5""! Allowed strategies are "password", "crypt", "sha512", "phpass"!
     */
    public function testInvalidPasswordAlgorithm()
    {
        $config = array(
            'ma27_api_key_authentication' => array(
                'user' => array(
                    'object_manager' => 'om',
                    'password'       => array(
                        'strategy' => 'md5',
                    ),
                ),
            ),
        );

        $configuration = new Configuration();
        $processor = new Processor();

        $processor->processConfiguration($configuration, $config);
    }

    public function testDefaultConfig()
    {
        $config = array(
            'ma27_api_key_authentication' => array(
                'user' => array(
                    'object_manager' => 'om',
                ),
            ),
        );

        $configuration = new Configuration();
        $processor = new Processor();

        $result = $processor->processConfiguration($configuration, $config);

        $this->assertSame(200, $result['user']['api_key_length']);
        $this->assertSame('om', $result['user']['object_manager']);

        $this->assertFalse($result['api_key_purge']['enabled']);
        $this->assertSame($result['key_header'], ApiKeyAuthenticator::API_KEY_HEADER);

        foreach ($result['services'] as $service) {
            $this->assertNull($service);
        }
    }
}
