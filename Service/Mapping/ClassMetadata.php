<?php

namespace Ma27\ApiKeyAuthenticationBundle\Service\Mapping;

use ReflectionProperty;

/**
 * Metadata object that contains the necessary data of the user model.
 *
 * @internal This code is part of the internal API to gather the appropriate model information and shouldn't be used for else use-cases
 */
class ClassMetadata
{
    const LOGIN_PROPERTY = 'login';
    const PASSWORD_PROPERTY = 'password';
    const API_KEY_PROPERTY = 'apiKey';
    const LAST_ACTION_PROPERTY = 'lastAction';

    /**
     * @var int[]
     */
    private static $requiredProperties = array(
        self::LOGIN_PROPERTY,
        self::PASSWORD_PROPERTY,
        self::API_KEY_PROPERTY,
    );

    /**
     * @var ReflectionProperty[]
     */
    private $properties = array();

    /**
     * @var string[]
     */
    private $lazyPropertyNameCache = array();

    /**
     * @var string[]
     */
    private $lazyValueCache = array();

    /**
     * Constructor.
     *
     * @param ReflectionProperty|string[] $properties
     *
     * @throws \InvalidArgumentException If one necessary property is missing
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;

        foreach (self::$requiredProperties as $property) {
            if (!isset($this->properties[$property])) {
                throw new \InvalidArgumentException(sprintf(
                    'Missing required property "%s"!',
                    $property
                ));
            }
        }
    }

    /**
     * Gets the value of the given property.
     *
     * @param object $user
     * @param string $property
     * @param bool   $strict
     *
     * @return mixed
     */
    public function getPropertyValue($user, $property = self::LOGIN_PROPERTY, $strict = false)
    {
        if ($this->checkProperty($property, $strict)) {
            $oid = spl_object_hash($user);
            if (null !== $cacheHit = $this->resolveCache($oid, $property)) {
                return $cacheHit;
            }

            if (is_string($this->properties[$property])) {
                $this->properties[$property] = new ReflectionProperty($user, $this->properties[$property]);
            }
            $this->properties[$property]->setAccessible(true);

            return $this->lazyValueCache[$oid][$property] = $this->properties[$property]->getValue($user);
        }
    }

    /**
     * Gets the name of a specific property by its metadata constant.
     *
     * @param string $property
     * @param bool   $strict
     *
     * @return null|string
     */
    public function getPropertyName($property = self::LOGIN_PROPERTY, $strict = false)
    {
        if ($this->checkProperty($property, $strict)) {
            if (is_string($this->properties[$property])) {
                return $this->properties[$property];
            }
            if (isset($this->lazyPropertyNameCache[$property])) {
                return $this->lazyPropertyNameCache[$property];
            }

            return $this->lazyPropertyNameCache[$property] = $this->properties[$property]->getName();
        }
    }

    /**
     * Modifies a property and clears the cache.
     *
     * @param object $user
     * @param mixed  $newValue
     * @param string $property
     */
    public function modifyProperty($user, $newValue, $property = self::LOGIN_PROPERTY)
    {
        $this->checkProperty($property, true);

        $propertyObject = $this->properties[$property];
        if (is_string($propertyObject)) {
            $this->properties[$property] = $propertyObject = new ReflectionProperty($user, $propertyObject);
        }

        $propertyObject->setAccessible(true);
        $propertyObject->setValue($user, $newValue);

        $oid = spl_object_hash($user);
        if (!array_key_exists($oid, $this->lazyValueCache)) {
            $this->lazyValueCache[$oid] = array();
        }

        $this->lazyValueCache[$oid][$property] = $newValue;
    }

    /**
     * Validates a property.
     *
     * @param string $property
     * @param bool   $strict
     *
     * @return bool
     */
    private function checkProperty($property = self::LOGIN_PROPERTY, $strict = false)
    {
        if (!isset($this->properties[$property])) {
            if ($strict) {
                throw new \LogicException(sprintf(
                    'Cannot get property "%s"!',
                    $property
                ));
            }

            return false;
        }

        return true;
    }

    /**
     * Resolves the lazy value cache.
     *
     * @param string $oid
     * @param string $property
     *
     * @return mixed|null
     */
    private function resolveCache($oid, $property)
    {
        if (isset($this->lazyValueCache[$oid])) {
            if (isset($this->lazyValueCache[$oid][$property])) {
                return $this->lazyValueCache[$oid][$property];
            }

            return;
        }

        $this->lazyValueCache[$oid] = array();
    }
}
