<?php

namespace Ma27\ApiKeyAuthenticationBundle\Event;

/**
 * Event to be triggered on invalid credentials.
 */
class OnInvalidCredentialsEvent extends AbstractUserEvent
{
    /**
     * Checks whether a user is available.
     * Helpful when writing a subscriber for the auth error (when providing an invalid username, no user can be loaded).
     *
     * @return bool
     */
    public function isUserAvailable()
    {
        return null !== $this->getUser();
    }
}
