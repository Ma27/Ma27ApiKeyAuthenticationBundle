<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\Password;

/**
 * Hasher which uses the crypt algorithm
 */
class CryptPasswordHasher implements PasswordHasherInterface
{
    /**
     * {@inheritdoc}
     */
    public function generateHash($password)
    {
        $salt = '$6$rounds=3000$' . base64_encode(uniqid()) . '$';
        return crypt($password, $salt);
    }

    /**
     * {@inheritdoc}
     */
    public function compareWith($password, $raw)
    {
        return crypt($raw, $password) === $password;
    }
}
