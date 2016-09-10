<?php

namespace Ma27\ApiKeyAuthenticationBundle\Service\Mapping\Driver;

/**
 * Generic provider which creates metadata objects for the user model.
 *
 * @internal This code is part of the internal API to gather the appropriate model information and shouldn't be used for else use-cases
 */
interface ModelConfigurationDriverInterface
{
    /**
     * @return \ReflectionProperty[]
     */
    public function getMetadataForUser();
}
