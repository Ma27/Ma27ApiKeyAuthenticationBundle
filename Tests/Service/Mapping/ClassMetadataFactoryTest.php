<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Service\Mapping;

use Ma27\ApiKeyAuthenticationBundle\Service\Mapping\ClassMetadata;
use Ma27\ApiKeyAuthenticationBundle\Service\Mapping\ClassMetadataFactory;
use Ma27\ApiKeyAuthenticationBundle\Tests\Resources\Entity\TestUser;
use Symfony\Component\Filesystem\Filesystem;

class ClassMetadataFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testResolveWithoutCache()
    {
        $user = new TestUser();
        $driver = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Service\\Mapping\\Driver\\ModelConfigurationDriverInterface');
        $driver->expects(self::once())
            ->method('getMetadataForUser')
            ->willReturn(array(
                ClassMetadata::LOGIN_PROPERTY    => new \ReflectionProperty($user, 'username'),
                ClassMetadata::PASSWORD_PROPERTY => new \ReflectionProperty($user, 'password'),
                ClassMetadata::API_KEY_PROPERTY  => new \ReflectionProperty($user, 'apiKey'),
            ));

        $factory = new ClassMetadataFactory($driver, new Filesystem(), false, null, 'Ma27\\ApiKeyAuthenticationBundle\\Tests\\Resources\\Entity\\TestUser');
        $metadata = $factory->createMetadataObject();

        $this->checkMetadata($metadata);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The file "/invalid/cache" can't be parsed!
     */
    public function testResolveIncompleteCache()
    {
        $driver = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Service\\Mapping\\Driver\\ModelConfigurationDriverInterface');
        $factory = new ClassMetadataFactory($driver, new Filesystem(), true, '/invalid/cache', 'Ma27\\ApiKeyAuthenticationBundle\\Tests\\Resources\\Entity\\TestUser');

        $factory->createMetadataObject();
    }

    public function testResolveCache()
    {
        $driver = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Service\\Mapping\\Driver\\ModelConfigurationDriverInterface');
        $driver->expects(self::never())
            ->method('getMetadataForUser');

        $fs = new Filesystem();
        $file = __DIR__ . '/../../Fixture/cache/dump';

        $fs->touch($file);
        $fs->dumpFile($file, serialize(array(ClassMetadata::LOGIN_PROPERTY => 'username', ClassMetadata::PASSWORD_PROPERTY => 'password', ClassMetadata::API_KEY_PROPERTY => 'apiKey')));

        $factory = new ClassMetadataFactory($driver, $fs, true, $file, 'Ma27\\ApiKeyAuthenticationBundle\\Tests\\Resources\\Entity\\TestUser');
        $metadata = $factory->createMetadataObject();

        $this->checkMetadata($metadata);

        $user = new TestUser();
        $user->setUsername('Ma27');
        self::assertSame('Ma27', $metadata->getPropertyValue($user, ClassMetadata::LOGIN_PROPERTY));

        $fs->remove($file);
    }

    private function checkMetadata(ClassMetadata $metadata)
    {
        self::assertInstanceOf('Ma27\\ApiKeyAuthenticationBundle\\Service\\Mapping\\ClassMetadata', $metadata);
        self::assertSame('username', $metadata->getPropertyName(ClassMetadata::LOGIN_PROPERTY));
        self::assertSame('password', $metadata->getPropertyName(ClassMetadata::PASSWORD_PROPERTY));
        self::assertSame('apiKey', $metadata->getPropertyName(ClassMetadata::API_KEY_PROPERTY));
    }
}
