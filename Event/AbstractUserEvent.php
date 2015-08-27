<?php

namespace Ma27\ApiKeyAuthenticationBundle\Event;

use Ma27\ApiKeyAuthenticationBundle\Model\User\UserInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Basic user event
 */
abstract class AbstractUserEvent extends Event
{
    /**
     * @var UserInterface
     */
    private $user;

    /**
     * Constructor
     *
     * @param UserInterface $user
     */
    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Returns the user
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
