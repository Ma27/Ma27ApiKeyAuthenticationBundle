<?php

namespace Ma27\ApiKeyAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Abstract event for job commands.
 */
abstract class AbstractJobEvent extends Event
{
    /**
     * @var object[]
     */
    private $affectedUsers = array();

    /**
     * Constructor.
     *
     * @param object[] $users
     */
    public function __construct(array $users)
    {
        $this->affectedUsers = $users;
    }

    /**
     * Returns all affected version.
     *
     * @return object[]
     */
    public function getAffectedUsers()
    {
        return $this->affectedUsers;
    }
}
