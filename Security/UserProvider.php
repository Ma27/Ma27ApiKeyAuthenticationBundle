<?php

namespace Ma27\ApiKeyAuthenticationBundle\Security;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Implementation of a user provider
 */
class UserProvider implements AdvancedUserProviderInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var string
     */
    private $modelName;

    /**
     * @var string
     */
    private $apiKeyProperty;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     * @param string $modelName
     * @param string $apiKeyProperty
     */
    public function __construct(
        ObjectManager $objectManager,
        $modelName,
        $apiKeyProperty
    ) {
        $this->om = $objectManager;
        $this->modelName = (string) $modelName;
        $this->apiKeyProperty = (string) $apiKeyProperty;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByApiKey($apiKey)
    {
        return $this->om->getRepository($this->modelName)->findOneBy(array($this->apiKeyProperty => (string) $apiKey));
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        throw new \BadMethodCallException('Not implemented!');
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        throw new \BadMethodCallException('Not implemented!');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return in_array('Ma27\\ApiKeyAuthenticationBundle\\Model\\User\\UserInterface', class_implements($class));
    }
}
