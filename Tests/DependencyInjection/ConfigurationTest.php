<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\DependencyInjection;

use Ma27\ApiKeyAuthenticationBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The user model properties must be unique! Duplicated items found: foo
     */
    public function testDuplicatedFields()
    {
        $config = array(
            'ma27_api_key_authentication' => array(
                'user' => array(
                    'object_manager' => 'foobar',
                    'properties'     => array('email' => 'foo', 'username' => 'foo'),
                ),
            ),
        );

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), $config);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Email and username cannot be null!
     */
    public function testEmptyEmailAndUsername()
    {
        $config = array(
            'ma27_api_key_authentication' => array(
                'user' => array(
                    'object_manager' => 'om',
                    'properties'     => array(
                        'username' => null,
                    ),
                ),
            ),
        );

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
        $config = array(
            'ma27_api_key_authentication' => array(
                'user' => array(
                    'object_manager' => 'om',
                    'properties'     => array(
                        'password' => array(
                            'strategy' => 'md5',
                        ),
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
                    'properties'     => array('username' => 'username'),
                ),
            ),
        );

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
