<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Resources\Entity;

use Ma27\ApiKeyAuthenticationBundle\Annotation as Auth;

/**
 * Simple test object.
 */
class IncompleteTestUser
{
    /**
     * @Auth\ApiKey
     */
    private $apiKey;
}
