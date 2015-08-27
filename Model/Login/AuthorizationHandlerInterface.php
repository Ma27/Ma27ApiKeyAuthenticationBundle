<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\Login;

use Ma27\ApiKeyAuthenticationBundle\Model\User\UserInterface;

/**
 * Interface of a handler that executes the authorization
 */
interface AuthorizationHandlerInterface
{
    /**
     * Authenticates the user
     *
     * @param string[] $credentials
     *
     * @return UserInterface
     */
    public function authenticate(array $credentials);

    /**
     * Removes the user session
     *
     * @param UserInterface $user
     * @param bool $purgeJob
     */
    public function removeSession(UserInterface $user, $purgeJob = false);
}
