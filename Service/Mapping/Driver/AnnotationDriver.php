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
        $loginProperty = $passwordProperty = $apiKeyProperty = $lastActionProperty = null;

        foreach ($properties as $reflectionProperty) {
            foreach (array('login', 'password', 'apiKey', 'lastAction') as $annotation) {
                $class = sprintf('Ma27\\ApiKeyAuthenticationBundle\\Annotation\\%s', ucfirst($annotation));
                $annotationObject = $this->reader->getPropertyAnnotation($reflectionProperty, $class);

                if ($annotationObject) {
                    switch ($annotation) {
                        case 'login':
                            $this->assertUnique($loginProperty);
                            $loginProperty = $reflectionProperty;
                            break;
                        case 'password':
                            $this->assertUnique($passwordProperty);
                            $passwordProperty = $reflectionProperty;
                            break;
                        case 'apiKey':
                            $this->assertUnique($apiKeyProperty);
                            $apiKeyProperty = $reflectionProperty;
                            break;
                        case 'lastAction':
                            $this->assertUnique($lastActionProperty);
                            $lastActionProperty = $reflectionProperty;
                    }

                    if ($loginProperty && $passwordProperty && $apiKeyProperty && $lastActionProperty) {
                        break;
                    }

                    continue;
                }
            }
        }

        if (!$loginProperty || !$passwordProperty || !$apiKeyProperty) {
            throw new \LogicException(sprintf(
                'A user class must have a "%s", "%s", "%s" annotation!',
                'Login',
                'Password',
                'ApiKey'
            ));
        }

        return array(
            ClassMetadata::LOGIN_PROPERTY       => $loginProperty,
            ClassMetadata::PASSWORD_PROPERTY    => $passwordProperty,
            ClassMetadata::API_KEY_PROPERTY     => $apiKeyProperty,
            ClassMetadata::LAST_ACTION_PROPERTY => $lastActionProperty,
        );
    }

    /**
     * Checks whether a property is already set.
     *
     * @param \ReflectionProperty $property
     */
    private function assertUnique(\ReflectionProperty $property = null)
    {
        if (!empty($property)) {
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
}