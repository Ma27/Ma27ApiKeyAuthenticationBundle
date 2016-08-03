<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RestrictedRouteWebTest extends WebTestCase
{
    public function testInvalidApiKey()
    {
        $client = static::createClient();
        $client->request('GET', '/restricted.html', array(), array(), array('HTTP_X-API-KEY' => 'invalid token'));

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }

    public function testAppropriateWorkflow()
    {
        $client = static::createClient();
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

    /**
     * @return \Ma27\ApiKeyAuthenticationBundle\Tests\Resources\Entity\TestUser
     */
    private function getFixtureUser()
    {
        return self::$kernel->getContainer()->get('doctrine')->getManager()->getRepository('Functional:TestUser')->findOneBy(array('username' => 'Ma27'));
    }
}
