<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Resources\Controller;

use Symfony\Component\HttpFoundation\Response;

class TestController
{
    public function helloAction()
    {
        return new Response('hello world!');
    }
}
