<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Model\Password;

use Ma27\ApiKeyAuthenticationBundle\Model\Password\Sha512PasswordHasher;

class Sha512PasswordHasherTest extends \PHPUnit_Framework_TestCase
{
    public function testCompareHashes()
    {
        $hasher = new Sha512PasswordHasher();
        $password = '123456';
        $hash = $hasher->generateHash($password);

        $this->assertNotSame($password, $hash);
        $this->assertTrue($hasher->compareWith($hash, $password));
    }
}
