<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Annotation\Driver;

use Doctrine\Common\Annotations\AnnotationReader;
use Ma27\ApiKeyAuthenticationBundle\Annotation\Driver\AnnotationDriver;
use Ma27\ApiKeyAuthenticationBundle\Service\Mapping\ClassMetadata;

class AnnotationDriverTest extends \PHPUnit_Framework_TestCase
{
    public function testExtractMetadata()
    {
        $driver = new AnnotationDriver(new AnnotationReader(), 'Ma27\\ApiKeyAuthenticationBundle\\Tests\\Resources\\Entity\\TestUser');
        $metadata = $driver->getMetadataForUser();

        $this->assertSame('username', $metadata->getPropertyName(ClassMetadata::LOGIN_PROPERTY));
        $this->assertSame('password', $metadata->getPropertyName(ClassMetadata::PASSWORD_PROPERTY));
        $this->assertSame('apiKey', $metadata->getPropertyName(ClassMetadata::API_KEY_PROPERTY));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage A user class must have a "Login", "Password", "ApiKey" annotation!
     */
    public function testIncompleteUser()
    {
        $driver = new AnnotationDriver(new AnnotationReader(), 'Ma27\\ApiKeyAuthenticationBundle\\Tests\\Resources\\Entity\\IncompleteTestUser');
        $driver->getMetadataForUser();
    }
}
