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
use Ma27\ApiKeyAuthenticationBundle\Model\User\UserInterface;
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
     * @var string
     */
    private $passwordProperty;

    /**
     * @var string
     */
    private $userProperty;

    /**
     * @var string
     */
    private $emailProperty;

    /**
     * Constructor.
     *
     * @param ObjectManager            $om
     * @param PasswordHasherInterface  $passwordHasher
     * @param KeyFactoryInterface      $keyFactory
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $modelName
     * @param string                   $passwordProperty
     * @param string                   $userProperty
     * @param string                   $emailProperty
     */
    public function __construct(
        ObjectManager $om,
        PasswordHasherInterface $passwordHasher,
        KeyFactoryInterface $keyFactory,
        EventDispatcherInterface $dispatcher,
        $modelName,
        $passwordProperty,
        $userProperty = null,
        $emailProperty = null
    ) {
        $this->om = $om;
        $this->passwordHasher = $passwordHasher;
        $this->keyFactory = $keyFactory;
        $this->eventDispatcher = $dispatcher;
        $this->modelName = (string) $modelName;
        $this->passwordProperty = (string) $passwordProperty;
        $this->userProperty = $userProperty;
        $this->emailProperty = $emailProperty;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(array $credentials)
    {
        if (null === $this->userProperty && null === $this->emailProperty) {
            throw new \InvalidArgumentException('Username property and email property must not be null!');
        }

        $criteria = array();
        if (null !== $this->userProperty) {
            if (!isset($credentials[$this->userProperty])) {
                throw new \InvalidArgumentException(
                    sprintf('Unable to required find property "%s" in credential array!', $this->userProperty)
                );
            }

            $criteria[$this->userProperty] = $credentials[$this->userProperty];
        }

        if (null !== $this->emailProperty) {
            if (!isset($credentials[$this->emailProperty])) {
                throw new \InvalidArgumentException(
                    sprintf('Unable to required find property "%s" in credential array!', $this->emailProperty)
                );
            }

            $criteria[$this->emailProperty] = $credentials[$this->emailProperty];
        }

        if (!isset($credentials[$this->passwordProperty])) {
            throw new \InvalidArgumentException(
                sprintf('Unable to find password property "%s" in credential set!', $this->passwordProperty)
            );
        }

        $objectRepository = $this->om->getRepository($this->modelName);
        /** @var UserInterface $object */
        $object = $objectRepository->findOneBy($criteria);

        if (null === $object || !$this->passwordHasher->compareWith($object->getPassword(), $credentials[$this->passwordProperty])) {
            $this->eventDispatcher->dispatch(Ma27ApiKeyAuthenticationEvents::CREDENTIAL_FAILURE, new OnInvalidCredentialsEvent($object));

            throw new CredentialException();
        }

        $this->eventDispatcher->dispatch(Ma27ApiKeyAuthenticationEvents::AUTHENTICATION, new OnAuthenticationEvent($object));

        $object->setApiKey($this->keyFactory->getKey());
        $this->om->merge($object);

        $this->om->flush();

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function removeSession(UserInterface $user, $purgeJob = false)
    {
        $user->removeApiKey();

        $event = new OnLogoutEvent($user);
        if ($purgeJob) {
            $event->markAsPurgeJob();
        }

        $this->eventDispatcher->dispatch(Ma27ApiKeyAuthenticationEvents::LOGOUT, $event);

        $this->om->merge($user);

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
     * Getter for the password property.
     *
     * @return string
     */
    protected function getPasswordProperty()
    {
        return $this->passwordProperty;
    }

    /**
     * Getter for the user property.
     *
     * @return string
     */
    protected function getUserProperty()
    {
        return $this->userProperty;
    }

    /**
     * Getter for the email property.
     *
     * @return string
     */
    protected function getEmailProperty()
    {
        return $this->emailProperty;
    }
}
