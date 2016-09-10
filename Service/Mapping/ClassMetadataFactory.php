<?php

namespace Ma27\ApiKeyAuthenticationBundle\Service\Mapping;

use Ma27\ApiKeyAuthenticationBundle\Service\Mapping\Driver\ModelConfigurationDriverInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * ClassMetadataFactory builds a metadata object from a driver or cache.
 *
 * @internal This code is part of the internal API to gather the appropriate model information and shouldn't be used for else use-cases
 */
final class ClassMetadataFactory
{
    /**
     * @var ModelConfigurationDriverInterface
     */
    private $driver;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var bool
     */
    private $isCacheEnabled;

    /**
     * @var string
     */
    private $cacheFile;

    /**
     * @var string
     */
    private $classname;

    /**
     * Constructor.
     *
     * @param ModelConfigurationDriverInterface $driver
     * @param Filesystem                        $filesystem
     * @param bool                              $isCacheEnabled
     * @param string                            $cacheFile
     */
    public function __construct(ModelConfigurationDriverInterface $driver, Filesystem $filesystem, $isCacheEnabled, $cacheFile, $className)
    {
        $this->driver = $driver;
        $this->filesystem = $filesystem;
        $this->isCacheEnabled = (bool) $isCacheEnabled;
        $this->cacheFile = $cacheFile;
        $this->classname = $className;
    }

    /**
     * Creates the class metadata object.
     *
     * @return ClassMetadata
     */
    public function createMetadataObject()
    {
        return new ClassMetadata($this->resolveProperties());
    }

    /**
     * Resolves the class properties.
     *
     * @return \ReflectionProperty[]
     */
    private function resolveProperties()
    {
        if ($this->isCacheEnabled) {
            return $this->resolveCache();
        }

        return $this->driver->getMetadataForUser();
    }

    /**
     * Fetches the data from the cache.
     *
     * @return \ReflectionProperty[]
     */
    private function resolveCache()
    {
        if (!$this->filesystem->exists($this->cacheFile)) {
            throw new \RuntimeException(sprintf(
                'The file "%s" can\'t be parsed!',
                $this->cacheFile
            ));
        }

        return $this->wakeupReflection(unserialize(file_get_contents($this->cacheFile)));
    }

    /**
     * Wakes up the cached data and connects it with the reflection API.
     *
     * @param array $cacheData
     *
     * @return \ReflectionProperty[]
     */
    private function wakeupReflection(array $cacheData)
    {
        $className = $this->classname;

        // TODO metadata should be able to deal with strings, too.
        return array_combine(
            $cacheData[0],
            array_map(function ($property) use ($className) {
                return new \ReflectionProperty($className, $property);
            }, $cacheData[1])
        );
    }
}
