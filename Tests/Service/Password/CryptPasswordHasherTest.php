<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Service\Password;

use Ma27\ApiKeyAuthenticationBundle\Service\Password\CryptPasswordHasher;

class CryptPasswordHasherTest extends \PHPUnit_Framework_TestCase
{
    public function testCompareHashes()
    {
        $hasher = new CryptPasswordHasher();
        $password = '123456';
        $hash = $hasher->generateHash($password);

        $this->assertNotSame($password, $hash);
        $this->assertTrue($hasher->compareWith($hash, $password));
    }
}
