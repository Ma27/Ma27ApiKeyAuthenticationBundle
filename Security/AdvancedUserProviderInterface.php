<?php

namespace Ma27\ApiKeyAuthenticationBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Enhanced provider interface for api key searches
 */
interface AdvancedUserProviderInterface extends UserProviderInterface
{
    /**
     * Loads a user by its api key
     *
     * @param string $apiKey
     *
     * @return \Ma27\ApiKeyAuthenticationBundle\Model\User\UserInterface
     */
    public function findUserByApiKey($apiKey);
}
