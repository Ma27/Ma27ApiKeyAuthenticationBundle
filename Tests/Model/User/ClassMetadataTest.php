<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Model\User;

use Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata;
use Ma27\ApiKeyAuthenticationBundle\Tests\Resources\Entity\TestUser;

class ClassMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleModelProperties()
    {
        $object   = new TestUser();
        $class    = get_class($object);
        $metadata = new ClassMetadata(new \ReflectionClass($object), $class, array(
            ClassMetadata::LOGIN_PROPERTY    => new \ReflectionProperty($class, 'username'),
            ClassMetadata::PASSWORD_PROPERTY => new \ReflectionProperty($class, 'password'),
            ClassMetadata::API_KEY_PROPERTY  => new \ReflectionProperty($class, 'apiKey'),
        ));

        $this->assertSame('username', $metadata->getPropertyName(ClassMetadata::LOGIN_PROPERTY));
        $this->assertNull($metadata->getPropertyValue($object, ClassMetadata::LOGIN_PROPERTY));

        $metadata->modifyProperty($object, 'foo', ClassMetadata::LOGIN_PROPERTY);
        $this->assertSame('foo', $metadata->getPropertyValue($object, ClassMetadata::LOGIN_PROPERTY));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missing required property "apiKey"!
     */
    public function testIncompleteUser()
    {
        $object = new TestUser();
        $class  = get_class($object);

        new ClassMetadata(new \ReflectionClass($object), $class, array(
            ClassMetadata::LOGIN_PROPERTY    => new \ReflectionProperty($class, 'username'),
            ClassMetadata::PASSWORD_PROPERTY => new \ReflectionProperty($class, 'password'),
        ));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot get property "7"!
     */
    public function testModifyInvalidValue()
    {
        $object   = new TestUser();
        $class    = get_class($object);
        $metadata = new ClassMetadata(new \ReflectionClass($object), $class, array(
            ClassMetadata::LOGIN_PROPERTY    => new \ReflectionProperty($class, 'username'),
            ClassMetadata::PASSWORD_PROPERTY => new \ReflectionProperty($class, 'password'),
            ClassMetadata::API_KEY_PROPERTY  => new \ReflectionProperty($class, 'apiKey'),
        ));

        $metadata->modifyProperty($object, 'foo', 7);
    }
}
