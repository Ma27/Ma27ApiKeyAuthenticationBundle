<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Service\Password;

use Ma27\ApiKeyAuthenticationBundle\Service\Password\PHPPasswordHasher;

class PhpPasswordHasherTest extends \PHPUnit_Framework_TestCase
{
    public function testCompareHashes()
    {
        $hasher = new PHPPasswordHasher();
        $password = '123456';
        $hash = $hasher->generateHash($password);

        $this->assertNotSame($password, $hash);
        $this->assertTrue($hasher->compareWith($hash, $password));
    }
}
