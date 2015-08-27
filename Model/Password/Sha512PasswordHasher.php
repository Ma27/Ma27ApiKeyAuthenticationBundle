<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\Password;

/**
 * Hasher using the sha512 algorithm
 */
class Sha512PasswordHasher implements PasswordHasherInterface
{
    const HMAC_KEY = '$|@(jf03i@+o39V9)Z>?/$@GKi983c';

    /**
     * {@inheritdoc}
     */
    public function generateHash($password)
    {
        return hash_hmac('sha512', $password, static::HMAC_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function compareWith($password, $raw)
    {
        return $this->generateHash($raw) === $password;
    }
}
