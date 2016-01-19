<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Controller;

use Ma27\ApiKeyAuthenticationBundle\Security\ApiKeyAuthenticator;
use Ma27\ApiKeyAuthenticationBundle\Tests\Resources\Entity\TestUser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiKeyControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $login = 'Ma27';
        $password = '123456';

        $kernel = static::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->get('doctrine.dbal.default_connection')->exec('DELETE FROM TestUser');

        $user = new TestUser();
        $user->setUsername($login);
        $user->setPassword($container->get('ma27_api_key_authentication.password.strategy')->generateHash($password));
        $user->setEmail('foo@example.org');

        $em = $container->get('doctrine.orm.default_entity_manager');
        $em->persist($user);
        $em->flush();
    }

    public function testRefusedCredentials()
    {
        $client = static::createClient();

        $client->request('POST', '/api-key.json', array('login' => 'foo', 'password' => 'foo'));
        $response = $client->getResponse();

        $bareResponse = json_decode($response->getContent(), true);

        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\JsonResponse', $response);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame($bareResponse['message'], 'Credentials refused!');
    }

    public function testLogin()
    {
        $client = static::createClient();

        $client->request('POST', '/api-key.json', array('login' => 'Ma27', 'password' => '123456'));
        $response = $client->getResponse();

        $content = json_decode($response->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('apiKey', $content);

        $client->request('GET', '/restricted.html', array(), array(), array('HTTP_'.ApiKeyAuthenticator::API_KEY_HEADER => $content['apiKey']));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testLogoutWithMissingApiKey()
    {
        $client = static::createClient();

        $client->request('DELETE', '/api-key.json');
        $response = $client->getResponse();

        $this->assertSame(400, $response->getStatusCode());

        $bareResponse = json_decode($response->getContent(), true);
        $this->assertSame('Missing api key header!', $bareResponse['message']);
    }

    public function testLogout()
    {
        $client = static::createClient();

        $client->request('POST', '/api-key.json', array('login' => 'Ma27', 'password' => '123456'));
        $response = json_decode($client->getResponse()->getContent(), true);

        $apiKey = $response['apiKey'];
        $client->request('GET', '/restricted.html', array(), array(), array('HTTP_'.ApiKeyAuthenticator::API_KEY_HEADER => $apiKey));
        $testResponse = $client->getResponse();
        $this->assertSame(200, $testResponse->getStatusCode());

        $client->request('DELETE', '/api-key.json', array(), array(), array('HTTP_'.ApiKeyAuthenticator::API_KEY_HEADER => $apiKey));
        $logoutResponse = $client->getResponse();

        $this->assertSame(204, $logoutResponse->getStatusCode());
    }
}
