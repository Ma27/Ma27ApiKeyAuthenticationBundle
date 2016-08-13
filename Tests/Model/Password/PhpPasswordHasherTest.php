<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Model\Password;

use Ma27\ApiKeyAuthenticationBundle\Model\Password\PhpPasswordHasher;

class PhpPasswordHasherTest extends \PHPUnit_Framework_TestCase
{
    public function testCompareHashes()
    {
        $hasher = new PhpPasswordHasher();
        $password = '123456';
        $hash = $hasher->generateHash($password);

        $this->assertNotSame($password, $hash);
        $this->assertTrue($hasher->compareWith($hash, $password));
    }
}
