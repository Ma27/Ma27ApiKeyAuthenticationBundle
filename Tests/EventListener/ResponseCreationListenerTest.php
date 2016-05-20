<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\EventListener;

use Ma27\ApiKeyAuthenticationBundle\Event\AssembleResponseEvent;
use Ma27\ApiKeyAuthenticationBundle\EventListener\ResponseCreationListener;
use Ma27\ApiKeyAuthenticationBundle\Exception\CredentialException;
use Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;

class ResponseCreationListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildAPIKeyResponse()
    {
        $user = new \stdClass();
        $key  = uniqid();

        $metadata = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $metadata->expects($this->once())
            ->method('getPropertyValue')
            ->with($user, ClassMetadata::API_KEY_PROPERTY)
            ->willReturn($key);

        $listener = new ResponseCreationListener(
            $this->getMock(TranslatorInterface::class),
            $metadata
        );

        $event = new AssembleResponseEvent($user);

        $listener->onResponseCreation($event);
        $response = $event->getResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);
        $this->assertSame($data['apiKey'], $key);
    }

    public function testAssembleErrors()
    {
        $user = null;
        $ex   = new CredentialException('Invalid username and password!');

        $translatedIntoGerman = 'Ungültige Zugangsdaten!';

        $metadata   = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $translator = $this->getMock(TranslatorInterface::class);
        $translator->expects($this->once())
            ->method('trans')
            ->with('Invalid username and password!')
            ->willReturn($translatedIntoGerman);

        $listener = new ResponseCreationListener(
            $translator,
            $metadata
        );

        $event = new AssembleResponseEvent($user, $ex);

        $listener->onResponseCreation($event);
        $response = $event->getResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);
        $this->assertSame($translatedIntoGerman, $data['message']);
    }
}
