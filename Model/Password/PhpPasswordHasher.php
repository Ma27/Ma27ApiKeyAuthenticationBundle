<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\Password;

/**
 * Concrete hasher that uses the php 5.5 api.
 */
class PhpPasswordHasher implements PasswordHasherInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        if (!function_exists('password_hash')) {
            throw new \RuntimeException(
                'In order to use this strategy please install the package "ircmaxell/password-compat" '.
                'or upgrade your php version to 5.5 or higher!'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateHash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
    }

    /**
     * {@inheritdoc}
     */
    public function compareWith($password, $raw)
    {
        return password_verify($raw, $password);
    }
}
