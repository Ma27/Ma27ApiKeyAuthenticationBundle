<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Functional;

use Ma27\ApiKeyAuthenticationBundle\Security\ApiKeyAuthenticator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RestrictedRouteWebTest extends WebTestCase
{
    public function testInvalidApiKey()
    {
        $client = static::createClient();
        $client->request('GET', '/restricted.html', [], [], ['HTTP_'.ApiKeyAuthenticator::API_KEY_HEADER => 'invalid token']);

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }
}
