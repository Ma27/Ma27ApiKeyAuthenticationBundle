<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\DependencyInjection;

use Ma27\ApiKeyAuthenticationBundle\DependencyInjection\Compiler\CompileHasherServicesPass;
use Ma27\ApiKeyAuthenticationBundle\DependencyInjection\Ma27ApiKeyAuthenticationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class Ma27ApiKeyAuthenticationExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testFullConfig()
    {
        $container = new ContainerBuilder();
        $extension = new Ma27ApiKeyAuthenticationExtension();

        $container->addCompilerPass(new CompileHasherServicesPass());
        $container->setDefinition('annotation_reader', new Definition('Doctrine\\Common\\Annotations\\Reader'));
        $container->setDefinition('translator', new Definition('Symfony\\Component\\Translation\\Translator'));

        $extension->load(
            array(
                'ma27_api_key_authentication' => array(
                    'user' => array(
                        'object_manager' => 'om',
                        'password'       => array(
                            'strategy' => 'crypt',
                        ),
                    ),
                    'api_key_purge' => array(
                        'enabled' => true,
                    ),
                    'services' => array(
                        'auth_handler' => 'foo.bar',
                    ),
                    'key_header' => 'HTTP_HEADER',
                ),
            ),
            $container
        );

        $container->setDefinition('foo.bar', new Definition('stdClass'));
        $container->setDefinition('event_dispatcher', new Definition('Symfony\\Component\\EventDispatcher\\EventDispatcher'));
        $container->setDefinition('om', new Definition(get_class($this->getMock('Doctrine\\Common\\Persistence\\ObjectManager'))));
        $container->setDefinition('request_stack', new Definition(get_class($this->getMock('Symfony\\Component\\HttpFoundation\\RequestStack'))));

        $container->compile();

        $this->assertSame((string) $container->getAlias('ma27_api_key_authentication.auth_handler'), 'foo.bar');
        $this->assertSame(
            'Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\CryptPasswordHasher',
            $container->getDefinition($container->getAlias('ma27_api_key_authentication.password.strategy'))->getClass()
        );

        $this->assertTrue($container->hasDefinition('ma27_api_key_authentication.api_key_purge.last_action_refresh_listener'));

        $this->assertNotNull($container->getDefinition('ma27_api_key_authentication.cleanup_command')->getArgument(0));
        $this->assertSame($container->getParameter('ma27_api_key_authentication.key_header'), 'HTTP_HEADER');

        $securityDefinition = $container->getDefinition('ma27_api_key_authentication.security.authenticator');
        $this->assertSame($securityDefinition->getArgument(4), 'HTTP_HEADER');

        $this->assertTrue($container->hasParameter('ma27_api_key_authentication.response_values'));
    }

    public function testDisabledPurger()
    {
        $container = new ContainerBuilder();
        $extension = new Ma27ApiKeyAuthenticationExtension();

        $container->addCompilerPass(new CompileHasherServicesPass());
        $container->setDefinition('annotation_reader', new Definition('Doctrine\\Common\\Annotations\\Reader'));
        $container->setDefinition('translator', new Definition('Symfony\\Component\\Translation\\Translator'));

        $extension->load(array(
            'ma27_api_key_authentication' => array(
                'user'          => array('object_manager' => 'om'),
                'api_key_purge' => array('enabled' => false),
            ),
        ), $container);

        $container->setDefinition('foo.bar', new Definition('stdClass'));
        $container->setDefinition('event_dispatcher', new Definition('Symfony\\Component\\EventDispatcher\\EventDispatcher'));
        $container->setDefinition('om', new Definition(get_class($this->getMock('Doctrine\\Common\\Persistence\\ObjectManager'))));
        $container->setDefinition('request_stack', new Definition(get_class($this->getMock('Symfony\\Component\\HttpFoundation\\RequestStack'))));

        $container->compile();

        $this->assertFalse($container->hasDefinition('ma27_api_key_authentication.cleanup_command'));
        $this->assertFalse($container->hasDefinition('ma27_api_key_authentication.api_key_purge.last_action_refresh_listener'));
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

        $container->addCompilerPass(new CompileHasherServicesPass());
        $container->setDefinition('annotation_reader', new Definition('Doctrine\\Common\\Annotations\\Reader'));
        $container->setDefinition('translator', new Definition('Symfony\\Component\\Translation\\Translator'));

        $extension->load(
            array(
                'ma27_api_key_authentication' => array(
                    'user' => array(
                        'object_manager' => 'om',
                        'password'       => array(
                            'strategy' => $strategyName,
                        ),
                    ),
                ),
            ),
            $container
        );

        $container->setDefinition('om', new Definition(get_class($this->getMock('Doctrine\\Common\\Persistence\\ObjectManager'))));
        $container->setDefinition('event_dispatcher', new Definition('Symfony\\Component\\EventDispatcher\\EventDispatcher'));

        $container->compile();

        $definition = $container->getDefinition($container->getAlias('ma27_api_key_authentication.password.strategy'));
        $this->assertSame($expectedClass, $definition->getClass());
        $this->assertSame($expectedArgs, $definition->getArguments());
        $this->assertSame(200, $container->getParameter('ma27_api_key_authentication.property.apiKeyLength', 200));
    }

    public function hashStrategyProvider()
    {
        return array(
            array('crypt', 'Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\CryptPasswordHasher', array()),
            array('php55', 'Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\PhpPasswordHasher', array(12)),
            array('phpass', 'Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\PHPassHasher', array(8)),
        );
    }
}
