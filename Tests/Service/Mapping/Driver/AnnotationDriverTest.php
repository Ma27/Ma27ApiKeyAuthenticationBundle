<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Service\Mapping\Driver;

use Doctrine\Common\Annotations\AnnotationReader;
use Ma27\ApiKeyAuthenticationBundle\Service\Mapping\ClassMetadata;
use Ma27\ApiKeyAuthenticationBundle\Service\Mapping\Driver\AnnotationDriver;
use Ma27\ApiKeyAuthenticationBundle\Tests\Resources\Entity\IncompleteTestUser;
use Ma27\ApiKeyAuthenticationBundle\Tests\Resources\Entity\TestUser;

class AnnotationDriverTest extends \PHPUnit_Framework_TestCase
{
    public function testExtractMetadata()
    {
        $driver = new AnnotationDriver(new AnnotationReader(), TestUser::class);
        $metadata = $driver->getMetadataForUser();

        self::assertCount(4, $metadata);
        self::assertSame('username', $metadata[ClassMetadata::LOGIN_PROPERTY]->getName());
        self::assertSame('password', $metadata[ClassMetadata::PASSWORD_PROPERTY]->getName());
        self::assertSame('apiKey', $metadata[ClassMetadata::API_KEY_PROPERTY]->getName());
        self::assertSame('lastAction', $metadata[ClassMetadata::LAST_ACTION_PROPERTY]->getName());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage A user class must have a "Login", "Password", "ApiKey" annotation!
     */
    public function testIncompleteUser()
    {
        $driver = new AnnotationDriver(new AnnotationReader(), IncompleteTestUser::class);
        $driver->getMetadataForUser();
    }
}
