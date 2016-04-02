<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Security;

use Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata;
use Ma27\ApiKeyAuthenticationBundle\Security\ApiKeyAuthenticator;
use Ma27\ApiKeyAuthenticationBundle\Tests\Resources\Entity\TestUser;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
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
        $request->headers->set('HTTP_HEADER', $apiKey);

        $apiKeyAuthenticator = $this->mockAuthenticator();
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
        $apiKeyAuthenticator = $this->mockAuthenticator();
        $apiKeyAuthenticator->createToken($request, $providerKey);
    }

    public function testTokenSupport()
    {
        $apiKeyAuthenticator = $this->mockAuthenticator();
        $providerKey = 'provider';
        $token = $this->getMockBuilder(
            'Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $token
            ->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue($providerKey));

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
        $user = new TestUser();
        $token = $this->getMock('Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface');

        $token
            ->expects($this->any())
            ->method('getCredentials')
            ->will($this->returnValue($apiKey));

        $or = $this->getMock('Doctrine\\Common\\Persistence\\ObjectRepository');
        $or
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($user));

        $mock = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $mock
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($or));

        $authenticator = new ApiKeyAuthenticator($mock, new EventDispatcher(), 'AppBundle:User', $this->getClassMetadata(), 'HTTP_HEADER');

        /** @var PreAuthenticatedToken $token */
        $token = $authenticator->authenticateToken($token, $this->getMock('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface'), $providerKey);

        $this->assertInstanceOf(
            'Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken',
            $token
        );

        $this->assertSame($user, $token->getUser());
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
            ->will($this->returnValue($apiKey));

        $provider = $this->getMock('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface');

        $or = $this->getMock('Doctrine\\Common\\Persistence\\ObjectRepository');
        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($or));

        $authenticator = new ApiKeyAuthenticator($om, new EventDispatcher(), 'AppBundle:User', $this->getClassMetadata(), 'HTTP_HEADER');
        $authenticator->authenticateToken($token, $provider, $providerKey);
    }

    public function testFailureHandler()
    {
        $username = 'admin';
        $token = $this->getMock('Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface');
        $token
            ->expects($this->any())
            ->method('getUsername')
            ->will($this->returnValue($username));

        $exception = new AuthenticationException();
        $exception->setToken($token);

        $authenticator = $this->mockAuthenticator();
        $response = $authenticator->onAuthenticationFailure(Request::create('/'), $exception);

        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\JsonResponse', $response);
        $this->assertSame($response->getStatusCode(), 401);
    }

    public function getCredentialSet()
    {
        return array(
            array(
                uniqid(),
                'anon.',
            ),
        );
    }

    /**
     * Creates an authenticator with mocked arguments.
     *
     * @return ApiKeyAuthenticator
     */
    private function mockAuthenticator()
    {
        return new ApiKeyAuthenticator($this->getMock('Doctrine\\Common\\Persistence\\ObjectManager'), new EventDispatcher(), 'AppBundle:User', $this->getClassMetadata(), 'HTTP_HEADER');
    }

    /**
     * ClassMetadata.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getClassMetadata()
    {
        $mock = $this->getMockBuilder('Ma27\\ApiKeyAuthenticationBundle\\Model\\User\\ClassMetadata')->disableOriginalConstructor()->getMock();
        $mock
            ->expects($this->any())
            ->method('getPropertyName')
            ->with(ClassMetadata::API_KEY_PROPERTY)
            ->willReturn('apiKey');

        return $mock;
    }
}
