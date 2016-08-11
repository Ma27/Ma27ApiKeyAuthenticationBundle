<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\DependencyInjection;

use Ma27\ApiKeyAuthenticationBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
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
        $this->assertSame($result['key_header'], 'X-API-KEY');

        foreach ($result['services'] as $service) {
            $this->assertNull($service);
        }

        $this->assertSame($result['response'], array(
            'api_key_property' => 'apiKey',
            'error_property'   => 'message',
        ));

        $this->assertSame($result['api_key_purge']['last_action_listener'], array(
            'enabled' => true,
        ));
    }

    /**
     * Avoid further regressions as #57
     */
    public function testCustomHasherService()
    {
        $config = array(
            'ma27_api_key_authentication' => array(
                'user' => array(
                    'object_manager' => 'om',
                    'password'       => array(
                        'strategy' => 'custom',
                    ),
                ),
            ),
        );

        $configuration = new Configuration();
        $processor = new Processor();

        $result = $processor->processConfiguration($configuration, $config);
        self::assertSame('custom', $result['user']['password']['strategy']);
    }
}
