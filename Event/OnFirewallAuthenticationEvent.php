<?php

namespace Ma27\ApiKeyAuthenticationBundle\Event;

use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

/**
 * Event to be triggered after the successful authentication.
 */
class OnFirewallAuthenticationEvent extends AbstractUserEvent
{
    /**
     * @var PreAuthenticatedToken
     */
    private $token;

    /**
     * Sets the auth token.
     *
     * @param PreAuthenticatedToken $token
     */
    public function setToken(PreAuthenticatedToken $token)
    {
        $this->token = $token;
    }

    /**
     * Returns the authentication token.
     *
     * @return PreAuthenticatedToken
     */
    public function getToken()
    {
        return $this->token;
    }
}
