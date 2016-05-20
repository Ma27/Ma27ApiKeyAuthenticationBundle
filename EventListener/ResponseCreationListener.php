<?php

namespace Ma27\ApiKeyAuthenticationBundle\EventListener;

use Ma27\ApiKeyAuthenticationBundle\Event\AssembleResponseEvent;
use Ma27\ApiKeyAuthenticationBundle\Ma27ApiKeyAuthenticationEvents;
use Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * ResponseCreationListener.
 *
 * Default listener which assembles the response for the API key request.
 */
class ResponseCreationListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ClassMetadata
     */
    private $metadata;

    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     * @param ClassMetadata       $classMetadata
     */
    public function __construct(TranslatorInterface $translator, ClassMetadata $classMetadata)
    {
        $this->translator = $translator;
        $this->metadata   = $classMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(Ma27ApiKeyAuthenticationEvents::ASSEMBLE_RESPONSE => array(
            array('onResponseCreation', -10)
        ));
    }

    /**
     * Assembles the response.
     *
     * @param AssembleResponseEvent $event
     */
    public function onResponseCreation(AssembleResponseEvent $event)
    {
        if ($event->isSuccess()) {
            $event->setResponse(new JsonResponse(array(
                'apiKey' => $this->metadata->getPropertyValue($event->getUser(), ClassMetadata::API_KEY_PROPERTY)
            )));

            return;
        }

        $event->setResponse(new JsonResponse(
            array('message' => $this->translator->trans($event->getException()->getMessage() ?: 'Credentials refused!')),
            JsonResponse::HTTP_UNAUTHORIZED
        ));
    }
}
