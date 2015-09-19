<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\Command;

use Ma27\ApiKeyAuthenticationBundle\Command\SessionCleanupCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SessionCleanupCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testPurgeOutdatedApiKeys()
    {
        $userList = $this->getSampleUsers();

        $repository = $this->getMock('Doctrine\\Common\\Persistence\\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue($userList));

        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $handler = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Login\\AuthenticationHandlerInterface');
        $handler
            ->expects($this->exactly(3))
            ->method('removeSession');

        $cmd = new SessionCleanupCommand(
            $om,
            $handler,
            new EventDispatcher(),
            'AppBundle:User',
            'latestActivation'
        );

        $tester = $this->createApplicationForCommand($cmd);

        $tester->execute(['command' => $cmd->getName()]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot remove session of invalid user!
     */
    public function testBrokenModel()
    {
        $class = new \stdClass();
        $class->latestActivation = new \DateTime('-6 days');
        $userList = [$class];

        $repository = $this->getMock('Doctrine\\Common\\Persistence\\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue($userList));

        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $om
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $om
            ->expects($this->once())
            ->method('clear');

        $handler = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Model\\Login\\AuthenticationHandlerInterface');
        $cmd = new SessionCleanupCommand(
            $om,
            $handler,
            new EventDispatcher(),
            'AppBundle:User',
            'latestActivation'
        );

        $tester = $this->createApplicationForCommand($cmd);

        $tester->execute(['command' => $cmd->getName()]);
    }

    /**
     * Returns a simple user list.
     *
     * @return array
     */
    private function getSampleUsers()
    {
        $result = [];
        for ($i = 0; $i < 5; $i++) {
            $user = $this->getMock('Ma27\\ApiKeyAuthenticationBundle\\Tests\\Fixture\\TestUserInterface');

            if ($i % 2 === 0) {
                $expr = '-6 days';
            } else {
                $expr = 'now';
            }

            $user
                ->expects($this->any())
                ->method('getLatestActivation')
                ->will($this->returnValue(new \DateTime($expr)));

            $user
                ->expects($this->any())
                ->method('getApiKey')
                ->will($this->returnValue(uniqid()));

            $result[] = $user;
        }

        return $result;
    }

    /**
     * Creates a command tester.
     *
     * @param Command $command
     *
     * @return CommandTester
     */
    private function createApplicationForCommand(Command $command)
    {
        $application = new Application();
        $application->add($command);

        return new CommandTester($application->find($command->getName()));
    }
}
