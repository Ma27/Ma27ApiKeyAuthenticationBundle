<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Model\Key;

use Ma27\ApiKeyAuthenticationBundle\Model\Key\KeyFactory;

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
            ->will($this->returnValue($or))
        ;

        $factory = new KeyFactory($om, 'AppBundle:User', 'apiKey');
        $key = $factory->getKey();

        $this->assertSame(400, strlen($key));
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
            ->will($this->returnValue($userMock))
        ;

        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->any())
            ->method('getRepository')
            ->with('AppBundle:User')
            ->will($this->returnValue($or))
        ;

        $factory = new KeyFactory($om, 'AppBundle:User', 'apiKey');
        $factory->getKey();
    }
}
