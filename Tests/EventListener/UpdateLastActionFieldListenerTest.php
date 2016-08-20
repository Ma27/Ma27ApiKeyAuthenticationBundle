<?php

namespace Ma27\ApiKeyAuthenticationBundle\Tests\EventListener;

use Ma27\ApiKeyAuthenticationBundle\Event\OnAuthenticationEvent;
use Ma27\ApiKeyAuthenticationBundle\Event\OnFirewallAuthenticationEvent;
use Ma27\ApiKeyAuthenticationBundle\EventListener\UpdateLastActionFieldListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UpdateLastActionFieldListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideFixtureData
     *
     * @param object $user
     * @param bool   $assertPersistence
     */
    public function testModifyProperty($event, $assertPersistence, $method)
    {
        $objectManager = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $objectManager
            ->expects($assertPersistence ? $this->once() : $this->never())
            ->method('persist');
        $objectManager
            ->expects($assertPersistence ? $this->once() : $this->never())
            ->method('flush');

        $classMetadata = $this->getMockBuilder('Ma27\\ApiKeyAuthenticationBundle\\Service\\Mapping\\ClassMetadata')->disableOriginalConstructor()->getMock();
        $classMetadata
            ->expects($this->once())
            ->method('modifyProperty');

        $requestStack = new RequestStack();
        $request = Request::create('/');
        $request->server->set('REQUEST_TIME', time());
        $requestStack->push($request);

        $listener = new UpdateLastActionFieldListener($objectManager, $classMetadata, $requestStack);

        $listener->{$method}($event);
    }

    public function provideFixtureData()
    {
        return array(
            array(new OnAuthenticationEvent(new \stdClass()), false, 'onAuthentication'),
            array(new OnFirewallAuthenticationEvent(new \stdClass()), true, 'onFirewallLogin'),
        );
    }
}
