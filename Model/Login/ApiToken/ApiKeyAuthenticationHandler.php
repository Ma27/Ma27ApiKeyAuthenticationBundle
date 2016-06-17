<?php

namespace Ma27\ApiKeyAuthenticationBundle\Model\Login\ApiToken;

use Doctrine\Common\Persistence\ObjectManager;
use Ma27\ApiKeyAuthenticationBundle\Event\OnAuthenticationEvent;
use Ma27\ApiKeyAuthenticationBundle\Event\OnInvalidCredentialsEvent;
use Ma27\ApiKeyAuthenticationBundle\Event\OnLogoutEvent;
use Ma27\ApiKeyAuthenticationBundle\Exception\CredentialException;
use Ma27\ApiKeyAuthenticationBundle\Ma27ApiKeyAuthenticationEvents;
use Ma27\ApiKeyAuthenticationBundle\Model\Key\KeyFactoryInterface;
use Ma27\ApiKeyAuthenticationBundle\Model\Login\AuthenticationHandlerInterface;
use Ma27\ApiKeyAuthenticationBundle\Model\Password\PasswordHasherInterface;
use Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Concrete handler for api key authorization.
 */
class ApiKeyAuthenticationHandler implements AuthenticationHandlerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var PasswordHasherInterface
     */
    private $passwordHasher;

    /**
     * @var KeyFactoryInterface
     */
    private $keyFactory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var string
     */
    private $modelName;

    /**
     * @var ClassMetadata
     */
    private $classMetadata;

    /**
     * Constructor.
     *
     * @param ObjectManager            $om
     * @param PasswordHasherInterface  $passwordHasher
     * @param KeyFactoryInterface      $keyFactory
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $modelName
     * @param ClassMetadata            $metadata
     */
    public function __construct(
        ObjectManager $om,
        PasswordHasherInterface $passwordHasher,
        KeyFactoryInterface $keyFactory,
        EventDispatcherInterface $dispatcher,
        $modelName,
        ClassMetadata $metadata
    ) {
        $this->om = $om;
        $this->passwordHasher = $passwordHasher;
        $this->keyFactory = $keyFactory;
        $this->eventDispatcher = $dispatcher;
        $this->modelName = (string) $modelName;
        $this->classMetadata = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(array $credentials)
    {
        $loginProperty = $this->classMetadata->getPropertyName(ClassMetadata::LOGIN_PROPERTY);
        $passwordProperty = $this->classMetadata->getPropertyName(ClassMetadata::PASSWORD_PROPERTY);

        if (!array_key_exists($passwordProperty, $credentials)) {
            throw new \InvalidArgumentException(
                sprintf('Unable to find password property "%s" in credential set!', $passwordProperty)
            );
        }

        if (!array_key_exists($loginProperty, $credentials)) {
            throw new \InvalidArgumentException(
                sprintf('Unable to find login property "%s" in credential set!', $loginProperty)
            );
        }

        $objectRepository = $this->om->getRepository($this->modelName);
        $object = $objectRepository->findOneBy(array($loginProperty => $credentials[$loginProperty]));

        if (null === $object || !$this->passwordHasher->compareWith($object->getPassword(), $credentials[$passwordProperty])) {
            $this->eventDispatcher->dispatch(Ma27ApiKeyAuthenticationEvents::CREDENTIAL_FAILURE, new OnInvalidCredentialsEvent($object));

            throw new CredentialException();
        }

        $this->eventDispatcher->dispatch(Ma27ApiKeyAuthenticationEvents::AUTHENTICATION, new OnAuthenticationEvent($object));

        $key = $this->classMetadata->getPropertyValue($object, ClassMetadata::API_KEY_PROPERTY);
        if (empty($key)) {
            $this->classMetadata->modifyProperty($object, $this->keyFactory->getKey(), ClassMetadata::API_KEY_PROPERTY);
        }

        $this->om->persist($object);

        $this->om->flush();

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function removeSession($user, $purgeJob = false)
    {
        $this->classMetadata->modifyProperty($user, null, ClassMetadata::API_KEY_PROPERTY);

        $event = new OnLogoutEvent($user);
        if ($purgeJob) {
            $event->markAsPurgeJob();
        }

        $this->eventDispatcher->dispatch(Ma27ApiKeyAuthenticationEvents::LOGOUT, $event);

        $this->om->persist($user);

        // on purge jobs one big flush will be commited to the db after the whole action
        if (!$purgeJob) {
            $this->om->flush();
        }
    }

    /**
     * Getter for the object manager.
     *
     * @return ObjectManager
     */
    protected function getOm()
    {
        return $this->om;
    }

    /**
     * Getter for the password hasher.
     *
     * @return PasswordHasherInterface
     */
    protected function getPasswordHasher()
    {
        return $this->passwordHasher;
    }

    /**
     * Getter for the key factory.
     *
     * @return KeyFactoryInterface
     */
    protected function getKeyFactory()
    {
        return $this->keyFactory;
    }

    /**
     * Getter for the dispatcher.
     *
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Getter for the model name.
     *
     * @return string
     */
    protected function getModelName()
    {
        return $this->modelName;
    }

    /**
     * @return ClassMetadata
     */
    public function getClassMetadata()
    {
        return $this->classMetadata;
    }
}
