<?php

namespace Ma27\ApiKeyAuthenticationBundle\CacheWarmer;

use Ma27\ApiKeyAuthenticationBundle\Service\Mapping\Driver\ModelConfigurationDriverInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * ClassMetadataPropertiesCacheWarmer is a cache warmer implementation which fills the cache with
 * the evaluated result of the properties.
 *
 * @internal This code is part of the internal API to gather the appropriate model information and shouldn't be used for else use-cases
 */
class ClassMetadataPropertiesCacheWarmer implements CacheWarmerInterface
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
     * Constructor.
     *
     * @param ModelConfigurationDriverInterface $driver
     * @param Filesystem $filesystem
     */
    public function __construct(ModelConfigurationDriverInterface $driver, Filesystem $filesystem)
    {
        $this->driver = $driver;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $bundleCacheDir = sprintf('%s/ma27_api_key_authentication', $cacheDir);

        $this->filesystem->mkdir($bundleCacheDir);
        $this->filesystem->touch($filename = sprintf('%s/metadata_dump', $bundleCacheDir));
        $this->filesystem->dumpFile($filename, $this->buildCacheData());
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * Builds the cache data.
     *
     * @return string
     */
    private function buildCacheData()
    {
        $metadata = $this->driver->getMetadataForUser();

        return serialize(array(
            array_keys($metadata),
            array_values(array_map(function (\ReflectionProperty $property) {
                return $property->getName();
            }, $metadata))
        ));
    }
}
