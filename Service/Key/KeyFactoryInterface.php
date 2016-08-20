<?php

namespace Ma27\ApiKeyAuthenticationBundle\Service\Key;

/**
 * Factory that generates api keys.
 */
interface KeyFactoryInterface
{
    /**
     * Generates a key.
     *
     * @return string
     */
    public function getKey();
}
