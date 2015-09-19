<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\Password;

use Hautelook\Phpass\PasswordHash;

/**
 * Hasher using the phpass framework.
 */
class PHPassHasher implements PasswordHasherInterface
{
    /**
     * @var PasswordHash
     */
    private $phpass;

    /**
     * Constructor.
     *
     * @param int $iterationLength
     */
    public function __construct($iterationLength = 8)
    {
        $this->phpass = new PasswordHash((int) $iterationLength, true);
    }

    /**
     * {@inheritdoc}
     */
    public function generateHash($password)
    {
        return $this->phpass->HashPassword($password);
    }

    /**
     * {@inheritdoc}
     */
    public function compareWith($password, $raw)
    {
        return $this->phpass->CheckPassword($raw, $password);
    }
}
