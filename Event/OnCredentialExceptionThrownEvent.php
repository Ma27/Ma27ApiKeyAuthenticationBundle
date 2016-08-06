<?php

namespace Ma27\ApiKeyAuthenticationBundle\Event;

use Ma27\ApiKeyAuthenticationBundle\Exception\CredentialException;

/**
 * OnCredentialExceptionThrownEvent.
 */
class OnCredentialExceptionThrownEvent extends OnInvalidCredentialsEvent
{
    /**
     * @var CredentialException
     */
    private $ex;

    /**
     * Constructor.
     *
     * @param CredentialException $ex
     * @param null|object         $user
     */
    public function __construct(CredentialException $ex, $user = null)
    {
        parent::__construct($user);
        $this->ex = $ex;
    }

    /**
     * @return CredentialException
     */
    public function getCredentialException()
    {
        return $this->ex;
    }
}
