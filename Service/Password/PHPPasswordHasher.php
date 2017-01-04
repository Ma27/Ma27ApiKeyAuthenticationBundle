<?php

namespace Ma27\ApiKeyAuthenticationBundle\Service\Password;

/**
 * Hasher implementation based on the `password_*` API introduced in PHP 5.5.
 */
class PHPPasswordHasher implements PasswordHasherInterface
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
