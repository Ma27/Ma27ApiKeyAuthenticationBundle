<?php

namespace Ma27\ApiKeyAuthenticationBundle\Event;

use Ma27\ApiKeyAuthenticationBundle\Model\User\UserInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Basic user event.
 */
abstract class AbstractUserEvent extends Event
{
    /**
     * @var UserInterface
     */
    private $user;

    /**
     * Constructor.
     *
     * @param UserInterface $user
     */
    public function __construct(UserInterface $user = null)
    {
        $this->user = $user;
    }

    /**
     * Returns the user.
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Checks whether a user is available.
     *
     * Helpful when writing a subscriber for the auth error (when providing an invalid username, no user can be loaded)
     *
     * @return bool
     */
    public function isUserAvailable()
    {
        return null !== $this->user;
    }
}
