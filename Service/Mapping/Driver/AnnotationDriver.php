<?php

namespace Ma27\ApiKeyAuthenticationBundle\Service\Mapping\Driver;

use Doctrine\Common\Annotations\Reader;
use Ma27\ApiKeyAuthenticationBundle\Service\Mapping\ClassMetadata;
use ReflectionClass;

/**
 * Annotation driver which parses the annotations of the user model instance.
 *
 * @internal This code is part of the internal API to gather the appropriate model information and shouldn't be used for else use-cases
 */
final class AnnotationDriver implements ModelConfigurationDriverInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var string
     */
    private $userClass;

    /**
     * Constructor.
     *
     * @param Reader $annotationReader
     * @param string $userClass
     */
    public function __construct(Reader $annotationReader, $userClass)
    {
        $this->reader = $annotationReader;
        $this->userClass = (string) $userClass;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException If one of the annotations is missing
     * @throws \LogicException If one property has multiple "auth" annotations
     */
    public function getMetadataForUser()
    {
        $reflection = new ReflectionClass($this->userClass);
        $properties = $reflection->getProperties();
        $metadata = [
            ClassMetadata::LOGIN_PROPERTY       => null,
            ClassMetadata::PASSWORD_PROPERTY    => null,
            ClassMetadata::API_KEY_PROPERTY     => null,
            ClassMetadata::LAST_ACTION_PROPERTY => null,
        ];

        foreach ($properties as $reflectionProperty) {
            foreach (['login', 'password', 'apiKey', 'lastAction'] as $annotation) {
                $class = sprintf('Ma27\\ApiKeyAuthenticationBundle\\Annotation\\%s', ucfirst($annotation));
                $annotationObject = $this->reader->getPropertyAnnotation($reflectionProperty, $class);

                if (!$annotationObject) {
                    continue;
                }

                switch ($annotation) {
                    case 'login':
                        $this->assertUnique($metadata[ClassMetadata::LOGIN_PROPERTY]);
                        $metadata[ClassMetadata::LOGIN_PROPERTY] = $reflectionProperty;
                        break;
                    case 'password':
                        $this->assertUnique($metadata[ClassMetadata::PASSWORD_PROPERTY]);
                        $metadata[ClassMetadata::PASSWORD_PROPERTY] = $reflectionProperty;
                        break;
                    case 'apiKey':
                        $this->assertUnique($metadata[ClassMetadata::API_KEY_PROPERTY]);
                        $metadata[ClassMetadata::API_KEY_PROPERTY] = $reflectionProperty;
                        break;
                    case 'lastAction':
                        $this->assertUnique($metadata[ClassMetadata::LAST_ACTION_PROPERTY]);
                        $metadata[ClassMetadata::LAST_ACTION_PROPERTY] = $reflectionProperty;
                        break;
                }

                if ($this->isMetadataFullyLoaded($metadata)) {
                    break 2;
                }
            }
        }

        if (!$metadata[ClassMetadata::LOGIN_PROPERTY] || !$metadata[ClassMetadata::PASSWORD_PROPERTY] || !$metadata[ClassMetadata::API_KEY_PROPERTY]) {
            throw new \LogicException('A user class must have a "Login", "Password", "ApiKey" annotation!');
        }

        return $metadata;
    }

    /**
     * Checks whether a property is already set.
     *
     * @param \ReflectionProperty $property
     *
     * @throws \InvalidArgumentException
     */
    private function assertUnique(\ReflectionProperty $property = null)
    {
        if (null !== $property) {
            throw $this->createDuplicateAnnotationException();
        }
    }

    /**
     * Creates the exception when.
     *
     * @return \InvalidArgumentException
     */
    private function createDuplicateAnnotationException()
    {
        return new \InvalidArgumentException('None of the Ma27\\ApiKeyAuthenticationBundle annotations can be declared twice!');
    }

    /**
     * Method which checks if all metadata annotations were loaded already.
     *
     * @param object[] $metadata
     *
     * @return bool
     */
    private function isMetadataFullyLoaded(array $metadata)
    {
        return $metadata[ClassMetadata::LOGIN_PROPERTY]
            && $metadata[ClassMetadata::PASSWORD_PROPERTY]
            && $metadata[ClassMetadata::API_KEY_PROPERTY]
            && $metadata[ClassMetadata::LAST_ACTION_PROPERTY];
    }
}
