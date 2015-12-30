<?php

namespace Ma27\ApiKeyAuthenticationBundle\Controller;

use Ma27\ApiKeyAuthenticationBundle\Exception\CredentialException;
use Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata;
use Ma27\ApiKeyAuthenticationBundle\Security\ApiKeyAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller which is responsible for the authentication routes.
 */
class ApiKeyController extends Controller
{
    /**
     * Requests an api key.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function requestApiKeyAction(Request $request)
    {
        /** @var \Ma27\ApiKeyAuthenticationBundle\Model\Login\AuthenticationHandlerInterface $authenticationHandler */
        $authenticationHandler = $this->get('ma27_api_key_authentication.auth_handler');
        /** @var ClassMetadata $metadata */
        $metadata = $this->get('ma27_api_key_authentication.class_metadata');

        $credentials = array();
        if ($username = $request->request->get('login')) {
            $credentials[$metadata->getPropertyName(ClassMetadata::LOGIN_PROPERTY)] = $username;
        }

        if ($password = $request->request->get('password')) {
            $credentials[$metadata->getPropertyName(ClassMetadata::PASSWORD_PROPERTY)] = $password;
        }

        try {
            $user = $authenticationHandler->authenticate($credentials);
        } catch (CredentialException $ex) {
            /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
            $translator = $this->get('translator');
            $errorMessage = $translator->trans($ex->getMessage() ?: 'Credentials refused!');

            return new JsonResponse(
                array('message' => $errorMessage),
                401
            );
        }

        return new JsonResponse(array('apiKey' => $metadata->getPropertyValue($user, ClassMetadata::API_KEY_PROPERTY)));
    }

    /**
     * Removes an api key.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeSessionAction(Request $request)
    {
        /** @var \Ma27\ApiKeyAuthenticationBundle\Model\Login\AuthenticationHandlerInterface $authenticationHandler */
        $authenticationHandler = $this->get('ma27_api_key_authentication.auth_handler');
        /** @var \Doctrine\Common\Persistence\ObjectManager $om */
        $om = $this->get($this->container->getParameter('ma27_api_key_authentication.object_manager'));
        /** @var ClassMetadata $metadata */
        $metadata = $this->get('ma27_api_key_authentication.class_metadata');

        if (!$header = (string) $request->headers->get(ApiKeyAuthenticator::API_KEY_HEADER)) {
            return new JsonResponse(array('message' => 'Missing api key header!'), 400);
        }

        $repository = $om->getRepository($this->container->getParameter('ma27_api_key_authentication.model_name'));
        $user = $repository->findOneBy(array($metadata->getPropertyName(ClassMetadata::API_KEY_PROPERTY) => (string) $header));

        $authenticationHandler->removeSession($user);

        return new JsonResponse(array(), 204);
    }
}
