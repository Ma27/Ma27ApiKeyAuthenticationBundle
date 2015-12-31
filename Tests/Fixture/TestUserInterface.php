<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Fixture;

interface TestUserInterface extends \Symfony\Component\Security\Core\User\UserInterface
{
    public function getLatestActivation();
}
