<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Model\Password;

use Ma27\ApiKeyAuthenticationBundle\Model\Password\PhpPasswordHasher;

class PhpPasswordHasherTest extends \PHPUnit_Framework_TestCase
{
    public function testCompareHashes()
    {
        if (!function_exists('password_hash')) {
            $this->markTestIncomplete(
                'Cannot test password hasher since the required api is not available on the this php installation!'
            );
        }

        $hasher = new PhpPasswordHasher();
        $password = '123456';
        $hash = $hasher->generateHash($password);

        $this->assertNotSame($password, $hash);
        $this->assertTrue($hasher->compareWith($hash, $password));
    }
}
