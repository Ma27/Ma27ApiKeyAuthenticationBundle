<?php

namespace Ma27\ApiKeyAuthenticationBundle\Service\Password;

/**
 * Model that provides method for a password hasher.
 */
interface PasswordHasherInterface
{
    /**
     * Generates the bare hash.
     *
     * @param string $password
     *
     * @return string
     */
    public function generateHash($password);

    /**
     * Compares the hash with a raw password.
     *
     * @param string $password
     * @param string $raw
     *
     * @return bool
     */
    public function compareWith($password, $raw);
}
