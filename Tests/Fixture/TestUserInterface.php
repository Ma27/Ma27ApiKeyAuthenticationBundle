<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Fixture;

use Ma27\ApiKeyAuthenticationBundle\Model\User\UserInterface;

interface TestUserInterface extends UserInterface
{
    public function getLatestActivation();
}
