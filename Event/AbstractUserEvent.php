<?php

namespace Ma27\ApiKeyAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Basic user event.
 */
abstract class AbstractUserEvent extends Event
{
    /**
     * @var object
     */
    private $user;

    /**
     * Constructor.
     *
     * @param object $user
     */
    public function __construct($user = null)
    {
        $this->user = $user;
    }

    /**
     * Returns the user.
     *
     * @return object
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
