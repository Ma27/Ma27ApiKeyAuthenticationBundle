<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

/**
 * WebTestCase is a base class for PHPUnit test cases and provides some utility for this bundle.
 */
abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * Builds a list of supported envs.
     *
     * @return array
     */
    public function getEnvs()
    {
        return array(
            array('dev'),
            array('cache'),
        );
    }

    /**
     * @return \Ma27\ApiKeyAuthenticationBundle\Tests\Resources\Entity\TestUser
     */
    protected function getFixtureUser()
    {
        return self::$kernel->getContainer()->get('doctrine')->getManager()->getRepository('Functional:TestUser')->findOneBy(array('username' => 'Ma27'));
    }

    /**
     * Builds a client with a given env.
     *
     * @param string $env
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function client($env)
    {
        return static::createClient(array('environment' => $env));
    }
}
