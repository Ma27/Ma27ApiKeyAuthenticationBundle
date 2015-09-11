<?php

namespace Ma27\ApiKeyAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event to be triggered on an api key error
 */
class OnApiKeyCleanupEvent extends Event
{
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * Constructor
     *
     * @param \Exception $exception
     */
    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
