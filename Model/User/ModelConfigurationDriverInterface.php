<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\User;

/**
 * Generic provider which creates metadata objects for the user model.
 */
interface ModelConfigurationDriverInterface
{
    /**
     * @return ClassMetadata
     */
    public function getMetadataForUser();
}
