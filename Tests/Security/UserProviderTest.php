<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Security;

use Ma27\ApiKeyAuthenticationBundle\Model\User\UserInterface;
use Ma27\ApiKeyAuthenticationBundle\Security\UserProvider;

class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testClassSupport()
    {
        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $provider = new UserProvider($om, 'AppBundle:User', 'apiKey');

        $this->assertFalse($provider->supportsClass('stdClass'));
        $this->assertTrue(
            $provider->supportsClass(
                get_class(new TestUser())
            )
        );
    }

    public function testFindByApiKey()
    {
        $mock = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\User\\UserInterface');

        $or = $this->getMock('Doctrine\\Common\\Persistence\\ObjectRepository');
        $or
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($mock));

        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($or));

        $provider = new UserProvider($om, 'AppBundle:User', 'apiKey');
        $this->assertSame($mock, $provider->findUserByApiKey(uniqid()));
    }
}

class TestUser implements UserInterface
{
    public function eraseCredentials()
    {
    }

    public function getEmail()
    {
    }

    public function getApiKey()
    {
    }

    public function getRoles()
    {
    }

    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
    }

    public function setApiKey($apiKey)
    {
    }

    public function removeApiKey()
    {
    }
}
