<?php

namespace Ma27\ApiKeyAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Abstract event for job commands.
 */
abstract class AbstractJobEvent extends Event
{
    /**
     * @var \Ma27\ApiKeyAuthenticationBundle\Model\User\UserInterface[]
     */
    private $affectedUsers = [];

    /**
     * Constructor.
     *
     * @param \Ma27\ApiKeyAuthenticationBundle\Model\User\UserInterface[] $users
     */
    public function __construct(array $users)
    {
        $this->affectedUsers = $users;
    }

    /**
     * Returns all affected version.
     *
     * @return \Ma27\ApiKeyAuthenticationBundle\Model\User\UserInterface[]
     */
    public function getAffectedUsers()
    {
        return $this->affectedUsers;
    }
}
