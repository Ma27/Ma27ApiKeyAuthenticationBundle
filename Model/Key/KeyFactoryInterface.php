<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\Key;

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
