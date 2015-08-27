<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Security;

use Ma27\ApiKeyAuthenticationBundle\Security\ApiKeyAuthenticator;
use Ma27\ApiKeyAuthenticationBundle\Security\UserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApiKeyAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getCredentialSet
     */
    public function testCreateToken($apiKey, $providerKey)
    {
        $request = Request::create('/');
        $request->headers->set(ApiKeyAuthenticator::API_KEY_HEADER, $apiKey);

        $apiKeyAuthenticator = new ApiKeyAuthenticator();
        /** @var PreAuthenticatedToken $token */
        $token = $apiKeyAuthenticator->createToken($request, $providerKey);

        $this->assertInstanceOf(
            'Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken',
            $token
        );

        $this->assertSame($apiKey, $token->getCredentials());
        $this->assertSame($providerKey, $token->getProviderKey());
    }

    /**
     * @dataProvider getCredentialSet
     * @expectedException \Symfony\Component\Security\Core\Exception\BadCredentialsException
     * @expectedExceptionMessage No ApiKey found in request!
     */
    public function testCreateTokenWithEmptyRequest($apiKey, $providerKey)
    {
        $request = Request::create('/');
        $apiKeyAuthenticator = new ApiKeyAuthenticator();
        $apiKeyAuthenticator->createToken($request, $providerKey);
    }

    public function testTokenSupport()
    {
        $apiKeyAuthenticator = new ApiKeyAuthenticator();
        $providerKey = 'provider';
        $token = $this->getMockBuilder(
            'Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken'
        )
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $token
            ->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue($providerKey))
        ;

        $this->assertTrue($apiKeyAuthenticator->supportsToken($token, $providerKey));
        $this->assertFalse($apiKeyAuthenticator->supportsToken($token, 'foo'));

        $this->assertFalse(
            $apiKeyAuthenticator->supportsToken(
                $this->getMock('Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface'),
                $providerKey
            )
        );
    }

    /**
     * @dataProvider getCredentialSet
     */
    public function testTokenAuthentication($apiKey, $providerKey)
    {
        $user = $this->getMockForAbstractClass('Ma27\\ApiKeyAuthenticationBundle\\Model\\User\\UserInterface');
        $token = $this->getMock('Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface');

        $token
            ->expects($this->any())
            ->method('getCredentials')
            ->will($this->returnValue($apiKey))
        ;

        $or = $this->getMock('Doctrine\\Common\\Persistence\\ObjectRepository');
        $or
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($user))
        ;

        $mock = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $mock
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($or))
        ;

        $provider = new UserProvider($mock, 'AppBundle:User', 'apiKey');
        $authenticator = new ApiKeyAuthenticator();

        /** @var PreAuthenticatedToken $token */
        $token = $authenticator->authenticateToken($token, $provider, $providerKey);

        $this->assertInstanceOf(
            'Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken',
            $token
        );

        $this->assertSame($user->getUsername(), $token->getUser()->getUsername());
        $this->assertSame($user->getPassword(), $token->getUser()->getPassword());
        $this->assertSame($apiKey, $token->getCredentials());
        $this->assertSame($providerKey, $token->getProviderKey());
    }

    /**
     * @dataProvider getCredentialSet
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessageRegExp /^API key \w+ does not exist!$/
     */
    public function testInvalidTokenAuthentication($apiKey, $providerKey)
    {
        $token = $this->getMock('Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface');
        $token
            ->expects($this->any())
            ->method('getCredentials')
            ->will($this->returnValue($apiKey))
        ;

        $provider = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Security\\AdvancedUserProviderInterface');
        $provider
            ->expects($this->any())
            ->method('findUserByApiKey')
            ->will($this->returnValue(false))
        ;

        $authenticator = new ApiKeyAuthenticator();
        $authenticator->authenticateToken($token, $provider, $providerKey);
    }

    public function testFailureHandler()
    {
        $username = 'admin';
        $token = $this->getMock('Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface');
        $token
            ->expects($this->any())
            ->method('getUsername')
            ->will($this->returnValue($username))
        ;

        $exception = new AuthenticationException();
        $exception->setToken($token);

        $authenticator = new ApiKeyAuthenticator();
        $response = $authenticator->onAuthenticationFailure(Request::create('/'), $exception);

        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\JsonResponse', $response);
        $this->assertSame($response->getStatusCode(), Response::HTTP_UNAUTHORIZED);

        $content = json_decode($response->getContent(), true);

        $this->assertSame($username, $content['username']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The api key provider must implement "Ma27\ApiKeyAuthenticationBundle\Security\AdvancedUserProviderInterface"!
     */
    public function testAuthenticationWithInvalidUserProvider()
    {
        $userProvider = new ApiKeyAuthenticator();
        $userProvider->authenticateToken(
            $this->getMock('Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface'),
            $this->getMock('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface'),
            'anon.'
        );
    }

    public function getCredentialSet()
    {
        return array(
            array(
                uniqid(),
                'anon.'
            )
        );
    }
}
