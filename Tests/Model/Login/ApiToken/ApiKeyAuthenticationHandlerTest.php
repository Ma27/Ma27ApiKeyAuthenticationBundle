<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Model\Login\ApiToken;

use Ma27\ApiKeyAuthenticationBundle\Model\Login\ApiToken\ApiKeyAuthenticationHandler;
use Ma27\ApiKeyAuthenticationBundle\Model\Password\CryptPasswordHasher;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ApiKeyAuthenticationHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Username property and email property must not be null!
     */
    public function testInvalidCredentialParameters()
    {
        $fullMock = $this->getMockBuilder(
            'Ma27\\ApiKeyAuthenticationBundle\\Model\\Login\\ApiToken\\ApiKeyAuthenticationHandler'
        );

        $mock = $fullMock->disableOriginalConstructor()->getMockForAbstractClass();

        $mock->authenticate(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to required find property "email" in credential array!
     */
    public function testMissingEmail()
    {
        $handler = new ApiKeyAuthenticationHandler(
            $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\PasswordHasherInterface'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Key\\KeyFactoryInterface'),
            new EventDispatcher(),
            'AppBundle:User',
            'password',
            null,
            'email'
        );

        $handler->authenticate(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to required find property "username" in credential array!
     */
    public function testMissingUsername()
    {
        $handler = new ApiKeyAuthenticationHandler(
            $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\PasswordHasherInterface'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Key\\KeyFactoryInterface'),
            new EventDispatcher(),
            'AppBundle:User',
            'password',
            'username',
            null
        );

        $handler->authenticate(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to find password property "password" in credential set!
     */
    public function testMissingPasswordParam()
    {
        $handler = new ApiKeyAuthenticationHandler(
            $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\PasswordHasherInterface'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Key\\KeyFactoryInterface'),
            new EventDispatcher(),
            'AppBundle:User',
            'password',
            'username',
            null
        );

        $handler->authenticate(array('username' => 'Ma27'));
    }

    /**
     * @expectedException \Ma27\ApiKeyAuthenticationBundle\Exception\CredentialException
     */
    public function testInvalidCredentials()
    {
        $user = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\User\\UserInterface');
        $user
            ->expects($this->any())
            ->method('getPassword')
            ->will($this->returnValue(crypt('foo', '$6$rounds=500$foo$')))
        ;

        $or = $this->getMock('Doctrine\\Common\\Persistence\\ObjectRepository');
        $or
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($user))
        ;

        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($or))
        ;

        $handler = new ApiKeyAuthenticationHandler(
            $om,
            new CryptPasswordHasher(),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Key\\KeyFactoryInterface'),
            new EventDispatcher(),
            'AppBundle:User',
            'password',
            null,
            'email'
        );

        $handler->authenticate(array('email' => 'ma27@example.org', 'password' => 'blah'));
    }

    public function testBuildApiKey()
    {
        $hasher = new CryptPasswordHasher();
        $key = uniqid();
        $user = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\User\\UserInterface');
        $user
            ->expects($this->once())
            ->method('setApiKey')
            ->with($key)
        ;

        $user
            ->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue($hasher->generateHash('123456')))
        ;

        $or = $this->getMock('Doctrine\\Common\\Persistence\\ObjectRepository');
        $or
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($user))
        ;

        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($or))
        ;

        $factory = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Key\\KeyFactoryInterface');
        $factory
            ->expects($this->any())
            ->method('getKey')
            ->will($this->returnValue($key))
        ;

        $handler = new ApiKeyAuthenticationHandler(
            $om,
            $hasher,
            $factory,
            new EventDispatcher(),
            'AppBundle:User',
            'password',
            null,
            'email'
        );

        $result = $handler->authenticate(array('email' => 'ma27@example.org', 'password' => '123456'));
        $this->assertSame($user, $result);
    }

    public function testLogout()
    {
        $user = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\User\\UserInterface');
        $user
            ->expects($this->once())
            ->method('removeApiKey')
        ;

        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->once())
            ->method('merge')
            ->with($user)
        ;

        $handler = new ApiKeyAuthenticationHandler(
            $om,
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\PasswordHasherInterface'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Key\\KeyFactoryInterface'),
            new EventDispatcher(),
            'AppBundle:User',
            'password',
            'username',
            null
        );

        $handler->removeSession($user);
    }

    public function testLogoutOnCleanup()
    {
        $user = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\User\\UserInterface');
        $user
            ->expects($this->once())
            ->method('removeApiKey')
        ;

        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->once())
            ->method('merge')
            ->with($user)
        ;

        $om
            ->expects($this->never())
            ->method('flush')
        ;

        $handler = new ApiKeyAuthenticationHandler(
            $om,
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\PasswordHasherInterface'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Key\\KeyFactoryInterface'),
            new EventDispatcher(),
            'AppBundle:User',
            'password',
            'username',
            null
        );

        $handler->removeSession($user, true);
    }
}
