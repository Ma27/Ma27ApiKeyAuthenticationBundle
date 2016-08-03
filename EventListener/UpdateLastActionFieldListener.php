<?php

namespace Ma27\ApiKeyAuthenticationBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Ma27\ApiKeyAuthenticationBundle\Event\AbstractUserEvent;
use Ma27\ApiKeyAuthenticationBundle\Event\OnAuthenticationEvent;
use Ma27\ApiKeyAuthenticationBundle\Event\OnFirewallAuthenticationEvent;
use Ma27\ApiKeyAuthenticationBundle\Ma27ApiKeyAuthenticationEvents;
use Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * UpdateLastActionFieldListener.
 */
class UpdateLastActionFieldListener implements EventSubscriberInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ClassMetadata
     */
    private $classMetadata;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Constructor.
     *
     * @param ObjectManager $om
     * @param ClassMetadata $metadata
     * @param RequestStack  $requestStack
     */
    public function __construct(ObjectManager $om, ClassMetadata $metadata, RequestStack $requestStack)
    {
        $this->om = $om;
        $this->classMetadata = $metadata;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Ma27ApiKeyAuthenticationEvents::AUTHENTICATION => array('onAuthentication', -20), // late event since custom listeners should be triggered earlier
            Ma27ApiKeyAuthenticationEvents::FIREWALL_LOGIN => 'onFirewallLogin',
        );
    }

    /**
     * Modifies the last action property on authentication.
     *
     * @param OnAuthenticationEvent $event
     */
    public function onAuthentication(OnAuthenticationEvent $event)
    {
        $this->doModify($event);
    }

    /**
     * Modifies the last action property on firewall authentication.
     *
     * @param OnFirewallAuthenticationEvent $event
     */
    public function onFirewallLogin(OnFirewallAuthenticationEvent $event)
    {
        $this->doModify($event);

        $this->om->persist($event->getUser());
        $this->om->flush();
    }

    /**
     * Modifies the last action property of a user object.
     *
     * @param AbstractUserEvent $event
     */
    private function doModify(AbstractUserEvent $event)
    {
        $this->classMetadata->modifyProperty(
            $event->getUser(),
            new \DateTime(sprintf('@%s', $this->requestStack->getMasterRequest()->server->get('REQUEST_TIME'))),
            ClassMetadata::LAST_ACTION_PROPERTY
        );
    }
}
