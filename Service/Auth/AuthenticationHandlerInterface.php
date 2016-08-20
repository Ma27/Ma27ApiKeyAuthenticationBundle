<?php

namespace Ma27\ApiKeyAuthenticationBundle\Service\Auth;

/**
 * Interface of a handler that executes the authorization.
 */
interface AuthenticationHandlerInterface
{
    /**
     * Authenticates the user.
     *
     * @param string[] $credentials
     *
     * @return object
     */
    public function authenticate(array $credentials);

    /**
     * Removes the user session.
     *
     * @param object $user
     * @param bool   $purgeJob
     */
    public function removeSession($user, $purgeJob = false);
}
