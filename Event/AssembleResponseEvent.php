<?php

namespace Ma27\ApiKeyAuthenticationBundle\Event;

use Ma27\ApiKeyAuthenticationBundle\Exception\CredentialException;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * AssembleResponseEvent.
 *
 * Event which handles response creation.
 */
class AssembleResponseEvent extends Event
{
    /**
     * @var CredentialException
     */
    private $exception;

    /**
     * @var null|object
     */
    private $user;

    /**
     * @var JsonResponse
     */
    private $response;

    /**
     * Constructor.
     *
     * @param object              $user
     * @param CredentialException $exception
     */
    public function __construct($user = null, CredentialException $exception = null)
    {
        $this->exception = $exception;
        $this->user      = $user;
    }

    /**
     * @return CredentialException
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return object
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->exception === null;
    }

    /**
     * @return JsonResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param JsonResponse $response
     */
    public function setResponse(JsonResponse $response)
    {
        $this->response = $response;
    }
}
