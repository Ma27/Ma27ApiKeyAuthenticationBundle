<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\User;

use ReflectionClass;
use ReflectionProperty;

/**
 * Metadata object that contains the necessary data of the user model.
 */
class ClassMetadata
{
    const LOGIN_PROPERTY = 1;
    const PASSWORD_PROPERTY = 2;
    const API_KEY_PROPERTY = 3;
    const LAST_ACTION_PROPERTY = 4;

    /**
     * @var int[]
     */
    private static $necessaryProperties = array(
        self::LOGIN_PROPERTY    => 'login',
        self::PASSWORD_PROPERTY => 'password',
        self::API_KEY_PROPERTY  => 'apiKey',
    );

    /**
     * @var ReflectionClass
     */
    private $reflectionInstance;

    /**
     * @var string
     */
    private $className;

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
     * @param ReflectionClass      $reflection
     * @param string               $className
     * @param ReflectionProperty[] $properties
     *
     * @throws \InvalidArgumentException If one necessary property is missing
     */
    public function __construct(ReflectionClass $reflection, $className, array $properties)
    {
        $this->reflectionInstance = $reflection;
        $this->className = (string) $className;
        $this->properties = $properties;

        foreach (self::$necessaryProperties as $propertyIndex => $alias) {
            if (!isset($this->properties[$propertyIndex])) {
                throw new \InvalidArgumentException(sprintf(
                    'Missing required property "%s"!',
                    $alias
                ));
            }
        }
    }

    /**
     * Gets the value of the given property.
     *
     * @param object $user
     * @param int    $property
     * @param bool   $strict
     *
     * @return mixed
     */
    public function getPropertyValue($user, $property = self::LOGIN_PROPERTY, $strict = false)
    {
        if ($this->checkProperty($property, $strict)) {
            $oid = spl_object_hash($user);
            if (isset($this->lazyValueCache[$oid])) {
                if (isset($this->lazyValueCache[$oid][$property])) {
                    return $this>$this->lazyValueCache[$oid][$property];
                }
            } else {
                $this->lazyValueCache[$oid] = array();
            }

            $this->properties[$property]->setAccessible(true);
            return $this->lazyValueCache[$oid][$property] = $this->properties[$property]->getValue($user);
        }

        return null;
    }

    /**
     * Gets the name of a specific property by its metadata constant.
     *
     * @param int  $property
     * @param bool $strict
     *
     * @return null|string
     */
    public function getPropertyName($property = self::LOGIN_PROPERTY, $strict = false)
    {
        if ($this->checkProperty($property, $strict)) {
            if (isset($this->lazyPropertyNameCache[$property])) {
                return $this->lazyPropertyNameCache[$property];
            }

            return $this->lazyPropertyNameCache[$property] = $this->properties[$property]->getName();
        }

        return null;
    }

    /**
     * Modifies a property and clears the cache.
     *
     * @param object $user
     * @param mixed  $newValue
     * @param int    $property
     */
    public function modifyProperty($user, $newValue, $property = self::LOGIN_PROPERTY)
    {
        $this->checkProperty($property, true);

        $propertyObject = $this->properties[$property];
        $propertyObject->setAccessible(true);
        $propertyObject->setValue($user, $newValue);

        unset($this->lazyValueCache[spl_object_hash($user)]);
    }

    /**
     * Validates a property.
     *
     * @param int  $property
     * @param bool $strict
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
}
