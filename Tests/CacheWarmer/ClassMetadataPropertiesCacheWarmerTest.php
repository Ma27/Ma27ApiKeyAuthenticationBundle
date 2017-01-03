<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\CacheWarmer;

use Ma27\ApiKeyAuthenticationBundle\CacheWarmer\ClassMetadataPropertiesCacheWarmer;

class ClassMetadataPropertiesCacheWarmerTest extends \PHPUnit_Framework_TestCase
{
    public function testWarmupCache()
    {
        $driver = $this->createMock('Ma27\\ApiKeyAuthenticationBundle\\Service\\Mapping\\Driver\\ModelConfigurationDriverInterface');
        $driver->expects(self::once())
            ->method('getMetadataForUser')
            ->willReturn(array('test' => new \ReflectionProperty(new TestCacheClass(), 'values')));

        $filesystem = $this->createMock('Symfony\\Component\\Filesystem\\Filesystem');
        $filesystem->expects(self::once())
            ->method('mkdir')
            ->with('/var/www/app/ma27_api_key_authentication');
        $filesystem->expects(self::once())
            ->method('touch')
            ->with('/var/www/app/ma27_api_key_authentication/metadata_dump');
        $filesystem->expects(self::once())
            ->method('dumpFile')
            ->with('/var/www/app/ma27_api_key_authentication/metadata_dump', 'a:1:{s:4:"test";s:6:"values";}');

        $cacheWarmer = new ClassMetadataPropertiesCacheWarmer($driver, $filesystem);

        // no further assertions needed, simply check how the filesystem API behaves
        $cacheWarmer->warmUp('/var/www/app');
    }
}

class TestCacheClass
{
    public $values;
}
