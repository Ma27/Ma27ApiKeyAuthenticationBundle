<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\DependencyInjection;

use Ma27\ApiKeyAuthenticationBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Email and username cannot be null!
     */
    public function testEmptyEmailAndUsername()
    {
        $config = [
            'ma27_api_key_authentication' => [
                'user' => [
                    'object_manager' => 'om',
                    'properties'     => [
                        'username' => null,
                    ],
                ],
            ],
        ];

        $configuration = new Configuration();
        $processor = new Processor();

        $processor->processConfiguration($configuration, $config);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "ma27_api_key_authentication.user.properties.password.strategy": Invalid password strategy ""md5""! Allowed strategies are "password", "crypt", "sha512", "phpass"!
     */
    public function testInvalidPasswordAlgorithm()
    {
        $config = [
            'ma27_api_key_authentication' => [
                'user' => [
                    'object_manager' => 'om',
                    'properties'     => [
                        'password' => [
                            'strategy' => 'md5',
                        ],
                    ],
                ],
            ],
        ];

        $configuration = new Configuration();
        $processor = new Processor();

        $processor->processConfiguration($configuration, $config);
    }

    public function testDefaultConfig()
    {
        $config = [
            'ma27_api_key_authentication' => [
                'user' => [
                    'object_manager' => 'om',
                    'properties'     => ['username' => 'username'],
                ],
            ],
        ];

        $configuration = new Configuration();
        $processor = new Processor();

        $result = $processor->processConfiguration($configuration, $config);

        $this->assertSame(200, $result['user']['api_key_length']);
        $this->assertSame('om', $result['user']['object_manager']);

        $this->assertFalse($result['api_key_purge']['enabled']);

        foreach ($result['services'] as $service) {
            $this->assertNull($service);
        }
    }
}
