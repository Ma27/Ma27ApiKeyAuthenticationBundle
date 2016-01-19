<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Model\Login\ApiToken;

use Ma27\ApiKeyAuthenticationBundle\Model\Login\ApiToken\ApiKeyAuthenticationHandler;
use Ma27\ApiKeyAuthenticationBundle\Model\Password\CryptPasswordHasher;
use Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata;
use Ma27\ApiKeyAuthenticationBundle\Tests\Resources\Entity\TestUser;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ApiKeyAuthenticationHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to find login property "login" in credential set!
     */
    public function testMissingLoginProperty()
    {
        $metadata = $this->getMetadata();

        $handler = new ApiKeyAuthenticationHandler(
            $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\PasswordHasherInterface'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Key\\KeyFactoryInterface'),
            new EventDispatcher(),
            'AppBundle:User',
            $metadata
        );

        $handler->authenticate(array('password' => 'foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to find password property "password" in credential set!
     */
    public function testMissingUsername()
    {
        $metadata = $this->getMetadata();

        $handler = new ApiKeyAuthenticationHandler(
            $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\PasswordHasherInterface'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Key\\KeyFactoryInterface'),
            new EventDispatcher(),
            'AppBundle:User',
            $metadata
        );

        $handler->authenticate(array('login' => 'foo'));
    }

    /**
     * @expectedException \Ma27\ApiKeyAuthenticationBundle\Exception\CredentialException
     */
    public function testInvalidCredentials()
    {
        $user = new TestUser();
        $user->setPassword(crypt('foo', '$6$rounds=500$foo$'));

        $or = $this->getMock('Doctrine\\Common\\Persistence\\ObjectRepository');
        $or
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($user));

        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($or));

        $handler = new ApiKeyAuthenticationHandler(
            $om,
            new CryptPasswordHasher(),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Key\\KeyFactoryInterface'),
            new EventDispatcher(),
            'AppBundle:User',
            $this->getMetadata()
        );

        $handler->authenticate(array('login' => 'ma27@example.org', 'password' => 'blah'));
    }

    public function testBuildApiKey()
    {
        $hasher = new CryptPasswordHasher();
        $key = uniqid();
        $user = new TestUser();
        $user->setPassword($hasher->generateHash('123456'));

        $or = $this->getMock('Doctrine\\Common\\Persistence\\ObjectRepository');
        $or
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($user));

        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($or));

        $factory = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Key\\KeyFactoryInterface');
        $factory
            ->expects($this->any())
            ->method('getKey')
            ->will($this->returnValue($key));

        $metadata = $this->getMetadata();
        $metadata
            ->expects($this->once())
            ->method('modifyProperty')
            ->with($user, $key, ClassMetadata::API_KEY_PROPERTY);

        $handler = new ApiKeyAuthenticationHandler(
            $om,
            $hasher,
            $factory,
            new EventDispatcher(),
            'AppBundle:User',
            $metadata
        );

        $result = $handler->authenticate(array('login' => 'ma27@example.org', 'password' => '123456'));
        $this->assertSame($user, $result);
    }

    public function testLogout()
    {
        $user = new TestUser();
        $metadata = $this->getMetadata(false);

        $metadata
            ->expects($this->once())
            ->method('modifyProperty')
            ->with($user, null, ClassMetadata::API_KEY_PROPERTY);

        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $handler = new ApiKeyAuthenticationHandler(
            $om,
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\PasswordHasherInterface'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Key\\KeyFactoryInterface'),
            new EventDispatcher(),
            'AppBundle:User',
            $metadata
        );

        $handler->removeSession($user);
    }

    public function testLogoutOnCleanup()
    {
        $user = new TestUser();
        $metadata = $this->getMetadata(false);

        $metadata
            ->expects($this->once())
            ->method('modifyProperty')
            ->with($user, null, ClassMetadata::API_KEY_PROPERTY);

        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $om
            ->expects($this->never())
            ->method('flush');

        $handler = new ApiKeyAuthenticationHandler(
            $om,
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Password\\PasswordHasherInterface'),
            $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Key\\KeyFactoryInterface'),
            new EventDispatcher(),
            'AppBundle:User',
            $metadata
        );

        $handler->removeSession($user, true);
    }

    /**
     * @param bool $expectLoginAndPassword
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMetadata($expectLoginAndPassword = true)
    {
        $metadata = $this->getMockBuilder('Ma27\\ApiKeyAuthenticationBundle\\Model\\User\\ClassMetadata')->disableOriginalConstructor()->getMock();

        if ($expectLoginAndPassword) {
            $metadata
                ->expects($this->at(0))
                ->method('getPropertyName')
                ->with(ClassMetadata::LOGIN_PROPERTY)
                ->willReturn('login');

            $metadata
                ->expects($this->at(1))
                ->method('getPropertyName')
                ->with(ClassMetadata::PASSWORD_PROPERTY)
                ->willReturn('password');
        }

        return $metadata;
    }
}
