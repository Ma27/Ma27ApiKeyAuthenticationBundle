<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\DependencyInjection;

use Ma27\ApiKeyAuthenticationBundle\DependencyInjection\Ma27ApiKeyAuthenticationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class Ma27ApiKeyAuthenticationExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testFullConfig()
    {
        $container = new ContainerBuilder();
        $extension = new Ma27ApiKeyAuthenticationExtension();

        $container->setDefinition('annotation_reader', new Definition('Doctrine\\Common\\Annotations\\Reader'));
        $container->setDefinition('translator', new Definition('Symfony\\Component\\Translation\\Translator'));

        $extension->load(
            array(
                'ma27_api_key_authentication' => array(
                    'user' => array(
                        'object_manager' => 'om',
                        'password'       => array(
                            'strategy' => 'sha512',
                        ),
                    ),
                    'api_key_purge' => array(
                        'enabled'   => true,
                        'log_state' => true,
                    ),
                    'services' => array(
                        'auth_handler' => 'foo.bar',
                    ),
                    'key_header' => 'HTTP_HEADER',
                ),
            ),
            $container
        );

        $logger = $this->getMock('Psr\\Log\\LogInterface');

        $container->setDefinition('foo.bar', new Definition('stdClass'));
        $container->setDefinition('logger', new Definition(get_class($logger)));
        $container->setDefinition('event_dispatcher', new Definition('Symfony\\Component\\EventDispatcher\\EventDispatcher'));
        $container->setDefinition('om', new Definition(get_class($this->getMock('Doctrine\\Common\\Persistence\\ObjectManager'))));

        $container->compile();

        $this->assertSame((string) $container->getAlias('ma27_api_key_authentication.auth_handler'), 'foo.bar');
        $this->assertSame(
            'Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\Sha512PasswordHasher',
            $container->getDefinition('ma27_api_key_authentication.password.strategy')->getClass()
        );

        $this->assertNotNull($container->getDefinition('ma27_api_key_authentication.cleanup_command')->getArgument(0));
        $this->assertSame($container->getParameter('ma27_api_key_authentication.key_header'), 'HTTP_HEADER');

        $securityDefinition = $container->getDefinition('ma27_api_key_authentication.security.authenticator');
        $this->assertSame($securityDefinition->getArgument(4), 'HTTP_HEADER');
    }

    /**
     * @dataProvider hashStrategyProvider
     *
     * @param string  $strategyName
     * @param string  $expectedClass
     * @param mixed[] $expectedArgs
     */
    public function testHashingStrategies($strategyName, $expectedClass, $expectedArgs)
    {
        $container = new ContainerBuilder();
        $extension = new Ma27ApiKeyAuthenticationExtension();

        $container->setDefinition('annotation_reader', new Definition('Doctrine\\Common\\Annotations\\Reader'));
        $container->setDefinition('translator', new Definition('Symfony\\Component\\Translation\\Translator'));

        $extension->load(
            array(
                'ma27_api_key_authentication' => array(
                    'user' => array(
                        'object_manager' => 'om',
                        'password'       => array(
                            'strategy'                => $strategyName,
                            'phpass_iteration_length' => 5,
                        ),
                    ),
                ),
            ),
            $container
        );

        $container->setDefinition('om', new Definition(get_class($this->getMock('Doctrine\\Common\\Persistence\\ObjectManager'))));
        $container->setDefinition('event_dispatcher', new Definition('Symfony\\Component\\EventDispatcher\\EventDispatcher'));

        $container->compile();

        $definition = $container->getDefinition('ma27_api_key_authentication.password.strategy');
        $this->assertSame($expectedClass, $definition->getClass());
        $this->assertSame($expectedArgs, $definition->getArguments());
    }

    public function hashStrategyProvider()
    {
        return array(
            array('crypt', 'Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\CryptPasswordHasher', array()),
            array('php55', 'Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\PhpPasswordHasher', array()),
            array('sha512', 'Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\Sha512PasswordHasher', array()),
            array('phpass', 'Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\PHPassHasher', array(5)),
        );
    }
}
