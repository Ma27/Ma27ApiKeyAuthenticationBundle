<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\User;

use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

/**
 * Simple interface that provides methods for a basic user model.
 */
interface UserInterface extends BaseUserInterface
{
    /**
     * Returns the email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Sets the api key.
     *
     * @param string $apiKey
     */
    public function setApiKey($apiKey);

    /**
     * Returns the api key.
     *
     * @return string
     */
    public function getApiKey();

    /**
     * Removes the api key.
     */
    public function removeApiKey();
}
