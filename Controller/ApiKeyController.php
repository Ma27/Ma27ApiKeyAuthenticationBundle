<?php

namespace Ma27\ApiKeyAuthenticationBundle\Controller;

use Ma27\ApiKeyAuthenticationBundle\Exception\CredentialException;
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

        $credentials = [];
        if ($username = $request->request->get('username')) {
            $credentials[$this->container->getParameter('ma27_api_key_authentication.property.username')] = $username;
        }

        if ($email = $request->request->get('email')) {
            $credentials[$this->container->getParameter('ma27_api_key_authentication.property.email')] = $email;
        }

        if ($password = $request->request->get('password')) {
            $credentials[$this->container->getParameter('ma27_api_key_authentication.property.password')] = $password;
        }

        try {
            $user = $authenticationHandler->authenticate($credentials);
        } catch (CredentialException $ex) {
            /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
            $translator = $this->get('translator');
            $errorMessage = $translator->trans($ex->getMessage() ?: 'Credentials refused!');

            return new JsonResponse(
                ['message' => $errorMessage],
                401
            );
        }

        return new JsonResponse(['apiKey' => $user->getApiKey()]);
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

        if (!$header = (string) $request->headers->get(ApiKeyAuthenticator::API_KEY_HEADER)) {
            return new JsonResponse(['message' => 'Missing api key header!'], 400);
        }

        $repository = $om->getRepository($this->container->getParameter('ma27_api_key_authentication.model_name'));
        /** @var \Ma27\ApiKeyAuthenticationBundle\Model\User\UserInterface $user */
        $user = $repository->findOneBy([$this->container->getParameter('ma27_api_key_authentication.property.apiKey') => (string) $header]);

        $authenticationHandler->removeSession($user);

        return new JsonResponse([], 204);
    }
}
