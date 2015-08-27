<?php

namespace Ma27\ApiKeyAuthenticationBundle\Event;

/**
 * Logout event
 */
class OnLogoutEvent extends AbstractUserEvent
{
    /**
     * @var bool
     */
    private $purgeJob = false;

    /**
     * Marks the logout as purge job of the session purger
     */
    public function markAsPurgeJob()
    {
        $this->purgeJob = true;
    }

    /**
     * Checks if the event is triggered inside a purge job
     *
     * @return bool
     */
    public function isPurgeJob()
    {
        return $this->purgeJob;
    }
}
