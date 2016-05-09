<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\User;

/**
 * Generic provider which creates metadata objects for the user model.
 *
 * @internal This code is part of the internal API to gather the appropriate model information and shouldn't be used for else use-cases
 */
interface ModelConfigurationDriverInterface
{
    /**
     * @return ClassMetadata
     */
    public function getMetadataForUser();
}
