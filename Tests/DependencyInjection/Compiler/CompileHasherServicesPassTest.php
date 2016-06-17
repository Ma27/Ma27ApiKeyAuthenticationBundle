<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\DependencyInjection\Compiler;

use Ma27\ApiKeyAuthenticationBundle\DependencyInjection\Compiler\CompileHasherServicesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class CompileHasherServicesPassTest extends \PHPUnit_Framework_TestCase
{
    public function testAssignPasswordHasher()
    {
        $container = $this->buildContainer();

        $exampleHasher = new Definition('Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\CryptPasswordHasher');
        $exampleHasher->addTag('ma27_api_key_authentication.password_hasher', array('alias' => 'crypt'));

        $container->setDefinition('crypt', $exampleHasher);

        $container->setParameter('ma27_api_key_authentication.password_hashing_service', 'crypt');

        $container->compile();

        $this->assertSame($exampleHasher, $container->getDefinition(
            $container->getAlias('ma27_api_key_authentication.password.strategy')
        ));
    }

    public function testNoHashParameterGiven()
    {
        $container = $this->buildContainer();
        $container->compile();

        $this->assertFalse($container->hasAlias('ma27_api_key_authentication.password.strategy'));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Service "crypt" can have the tag "ma27_api_key_authentication.password_hasher" only one time!
     */
    public function testMultipleTagsAreGiven()
    {
        $container = $this->buildContainer();

        $exampleHasher = new Definition('Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\CryptPasswordHasher');
        $exampleHasher->addTag('ma27_api_key_authentication.password_hasher', array('alias' => 'crypt'));
        $exampleHasher->addTag('ma27_api_key_authentication.password_hasher', array('alias' => 'crypt'));

        $container->setParameter('ma27_api_key_authentication.password_hashing_service', 'crypt');
        $container->setDefinition('crypt', $exampleHasher);

        $container->compile();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The tag "ma27_api_key_authentication.password_hasher" on service "crypt" needs an `alias` property!
     */
    public function testMissingAliasProperty()
    {
        $container = $this->buildContainer();

        $exampleHasher = new Definition('Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\CryptPasswordHasher');
        $exampleHasher->addTag('ma27_api_key_authentication.password_hasher', array());

        $container->setParameter('ma27_api_key_authentication.password_hashing_service', 'crypt');
        $container->setDefinition('crypt', $exampleHasher);

        $container->compile();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage No service found for hashing alias "phpass"!
     */
    public function testUnknownAlias()
    {
        $container = $this->buildContainer();

        $exampleHasher = new Definition('Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\CryptPasswordHasher');
        $exampleHasher->addTag('ma27_api_key_authentication.password_hasher', array('alias' => 'crypt'));

        $container->setParameter('ma27_api_key_authentication.password_hashing_service', 'phpass');
        $container->setDefinition('crypt', $exampleHasher);

        $container->compile();
    }

    /**
     * @return ContainerBuilder
     */
    private function buildContainer()
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new CompileHasherServicesPass());

        return $container;
    }
}
