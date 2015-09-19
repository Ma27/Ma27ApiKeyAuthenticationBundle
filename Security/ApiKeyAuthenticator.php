<?php

namespace Ma27\ApiKeyAuthenticationBundle\Security;

use Doctrine\Common\Persistence\ObjectManager;
use Ma27\ApiKeyAuthenticationBundle\Event\OnFirewallAuthenticationEvent;
use Ma27\ApiKeyAuthenticationBundle\Event\OnFirewallFailureEvent;
use Ma27\ApiKeyAuthenticationBundle\Ma27ApiKeyAuthenticationEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * Concrete implementation of an authentication with an api key.
 */
class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    /**
     * @var string
     */
    const API_KEY_HEADER = 'X-API-KEY';

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $modelName;

    /**
     * @var string
     */
    private $apiKeyProperty;

    /**
     * Constructor.
     *
     * @param ObjectManager            $om
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $modelName
     * @param string                   $apiKeyProperty
     */
    public function __construct(ObjectManager $om, EventDispatcherInterface $dispatcher, $modelName, $apiKeyProperty)
    {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->modelName = (string) $modelName;
        $this->apiKeyProperty = (string) $apiKeyProperty;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(array(), 401);
    }

    /**
     * Returns an authenticated token.
     *
     * @param TokenInterface        $token
     * @param UserProviderInterface $userProvider
     * @param string                $providerKey
     *
     * @throws AuthenticationException If the api key does not exist or is invalid
     * @throws \RuntimeException       If $userProvider is not an instance of AdvancedUserProviderInterface
     *
     * @return PreAuthenticatedToken
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $apiKey = $token->getCredentials();

        if (!$user = $this->om->getRepository($this->modelName)->findOneBy(array($this->apiKeyProperty => (string) $apiKey))) {
            $this->dispatcher->dispatch(Ma27ApiKeyAuthenticationEvents::FIREWALL_FAILURE, new OnFirewallFailureEvent());

            throw new AuthenticationException(
                sprintf('API key %s does not exist!', $apiKey)
            );
        }

        $token = new PreAuthenticatedToken(
            $user,
            $apiKey,
            $providerKey,
            $user->getRoles() ?: array()
        );

        $firewallEvent = new OnFirewallAuthenticationEvent($user);
        $firewallEvent->setToken($token);

        $this->dispatcher->dispatch(Ma27ApiKeyAuthenticationEvents::FIREWALL_LOGIN, $firewallEvent);

        return $token;
    }

    /**
     * Checks if the token is supported.
     *
     * @param TokenInterface $token
     * @param string         $providerKey
     *
     * @return bool
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $providerKey === $token->getProviderKey();
    }

    /**
     * Creates an api key by the http request.
     *
     * @param Request $request
     * @param string  $providerKey
     *
     * @throws BadCredentialsException If the request token cannot be found
     *
     * @return PreAuthenticatedToken
     */
    public function createToken(Request $request, $providerKey)
    {
        $apiKey = $request->headers->get(self::API_KEY_HEADER);

        if (!$apiKey) {
            throw new BadCredentialsException('No ApiKey found in request!');
        }

        return new PreAuthenticatedToken(
            'unauthorized',
            $apiKey,
            $providerKey
        );
    }
}
