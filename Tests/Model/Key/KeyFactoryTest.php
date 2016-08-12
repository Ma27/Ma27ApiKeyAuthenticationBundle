<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Model\Key;

use Ma27\ApiKeyAuthenticationBundle\Model\Key\KeyFactory;
use Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata;

class KeyFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateKey()
    {
        $or = $this->getMock('Doctrine\\Common\\Persistence\\ObjectRepository');
        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->any())
            ->method('getRepository')
            ->with('AppBundle:User')
            ->will($this->returnValue($or));

        $factory = new KeyFactory($om, 'AppBundle:User', $this->getClassMetadata());
        $key = $factory->getKey();

        $this->assertSame(200, strlen($key));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to generate a new api key, stopping after 200 tries!
     */
    public function testKeyGenerationFailure()
    {
        $userMock = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\User\\UserInterface');
        $or = $this->getMock('Doctrine\\Common\\Persistence\\ObjectRepository');
        $or
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($userMock));

        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->any())
            ->method('getRepository')
            ->with('AppBundle:User')
            ->will($this->returnValue($or));

        $factory = new KeyFactory($om, 'AppBundle:User', $this->getClassMetadata());
        $factory->getKey();
    }

    /**
     * ClassMetadata.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getClassMetadata()
    {
        $mock = $this->getMockBuilder('Ma27\\ApiKeyAuthenticationBundle\\Model\\User\\ClassMetadata')->disableOriginalConstructor()->getMock();
        $mock
            ->expects($this->any())
            ->method('getPropertyName')
            ->with(ClassMetadata::API_KEY_PROPERTY)
            ->willReturn('apiKey');

        return $mock;
    }
}
