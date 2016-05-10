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
}
