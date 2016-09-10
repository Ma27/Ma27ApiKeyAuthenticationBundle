<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Functional;

use Ma27\ApiKeyAuthenticationBundle\Tests\WebTestCase;

class RestrictedRouteWebTest extends WebTestCase
{
    /**
     * @dataProvider getEnvs
     */
    public function testInvalidApiKey($env)
    {
        $client = $this->client($env);
        $client->request('GET', '/restricted.html', array(), array(), array('HTTP_X-API-KEY' => 'invalid token'));

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider getEnvs
     */
    public function testAppropriateWorkflow($env)
    {
        $client = $this->client($env);
        $client->request('POST', '/api-key.json', array('login' => 'Ma27', 'password' => '123456'));
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        // clear the last action to prove that it will be reloaded properly during the login
        $testUser = $this->getFixtureUser();
        $testUser->clearLastAction();
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($testUser);
        $em->flush();

        $key = $content['apiKey'];
        $client->request('GET', '/restricted.html', array(), array(), array('HTTP_X-API-KEY' => $key));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $user = $this->getFixtureUser();
        $this->assertNotEmpty($user->getLastAction());
    }
}
