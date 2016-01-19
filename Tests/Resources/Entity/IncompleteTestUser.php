<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Resources\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ma27\ApiKeyAuthenticationBundle\Annotation as Auth;

/**
 * @ORM\Entity()
 */
class IncompleteTestUser
{
    /**
     * @Auth\ApiKey
     */
    private $apiKey;
}
