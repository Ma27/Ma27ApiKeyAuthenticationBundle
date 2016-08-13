<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\Password;

/**
 * Concrete hasher that uses the php 5.5 api.
 */
class PhpPasswordHasher implements PasswordHasherInterface
{
    /**
     * @var int
     */
    private $cost;

    /**
     * Constructor.
     *
     * @param int $cost
     */
    public function __construct($cost = 12)
    {
        if (!function_exists('password_hash')) {
            throw new \RuntimeException(
                'In order to use this strategy please install the package "ircmaxell/password-compat" '.
                'or upgrade your php version to 5.5 or higher!'
            );
        }

        $this->cost = (int) $cost;
    }

    /**
     * {@inheritdoc}
     */
    public function generateHash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, array('cost' => $this->cost));
    }

    /**
     * {@inheritdoc}
     */
    public function compareWith($password, $raw)
    {
        return password_verify($raw, $password);
    }
}
