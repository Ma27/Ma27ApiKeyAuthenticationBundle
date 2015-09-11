<?php

namespace Ma27\ApiKeyAuthenticationBundle\Controller;

use Ma27\ApiKeyAuthenticationBundle\Exception\CredentialException;
use Ma27\ApiKeyAuthenticationBundle\Security\ApiKeyAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Controller which is responsible for the authentication routes
 */
class ApiKeyController extends Controller
{
    /**
     * Requests an api key
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function requestApiKeyAction(Request $request)
    {
        /** @var \Ma27\ApiKeyAuthenticationBundle\Model\Login\AuthorizationHandlerInterface $authorizationHandler */
        $authorizationHandler = $this->get('ma27_api_key_authentication.auth_handler');

        $credentials = array();
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
            $user = $authorizationHandler->authenticate($credentials);
        } catch (CredentialException $ex) {
            /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
            $translator = $this->get('translator');
            $errorMessage = $translator->trans($ex->getMessage() ?: 'Unable to grant access with the given credentials!');

            return new JsonResponse(
                array('message' => $errorMessage),
                401
            );
        }

        return new JsonResponse(array('apiKey' => $user->getApiKey()));
    }

    /**
     * Removes an api key
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeSessionAction(Request $request)
    {
        /** @var \Ma27\ApiKeyAuthenticationBundle\Model\Login\AuthorizationHandlerInterface $authorizationHandler */
        $authorizationHandler = $this->get('ma27_api_key_authentication.auth_handler');
        /** @var \Doctrine\Common\Persistence\ObjectManager $om */
        $om = $this->get($this->container->getParameter('ma27_api_key_authentication.object_manager'));

        if (!$header = (string) $request->headers->get(ApiKeyAuthenticator::API_KEY_HEADER)) {
            throw new HttpException(400, 'Missing api key header!');
        }

        $repository = $om->getRepository($this->container->getParameter('ma27_api_key_authentication.model_name'));
        /** @var \Ma27\ApiKeyAuthenticationBundle\Model\User\UserInterface $user */
        $user = $repository->findOneBy(array($this->container->getParameter('ma27_api_key_authentication.property.apiKey') => (string) $header));

        $authorizationHandler->removeSession($user);

        return new JsonResponse(array(), 204);
    }
}
