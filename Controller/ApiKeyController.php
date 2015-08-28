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
        $authorizationHandler = $this->get('ma27.auth.service.auth_handler');

        $credentials = array();
        if ($username = $request->request->get('username')) {
            $credentials[$this->container->getParameter('ma27.auth.property.username')] = $username;
        }

        if ($email = $request->request->get('email')) {
            $credentials[$this->container->getParameter('ma27.auth.property.email')] = $email;
        }

        if ($password = $request->request->get('password')) {
            $credentials[$this->container->getParameter('ma27.auth.property.password')] = $password;
        }

        try {
            $user = $authorizationHandler->authenticate($credentials);
        } catch (CredentialException $ex) {
            return new JsonResponse(
                array('message' => $ex->getMessage() ?: 'Unable to grant access with the given credentials!'),
                Response::HTTP_UNAUTHORIZED
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
        $authorizationHandler = $this->get('ma27.auth.service.auth_handler');
        /** @var \Ma27\ApiKeyAuthenticationBundle\Security\AdvancedUserProviderInterface $userProvider */
        $userProvider = $this->get('ma27.auth.service.security.user_provider');

        if (!$header = (string) $request->headers->get(ApiKeyAuthenticator::API_KEY_HEADER)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Missing api key header!');
        }

        $authorizationHandler->removeSession($userProvider->findUserByApiKey($header));

        return new JsonResponse(array(), Response::HTTP_NO_CONTENT);
    }
}
