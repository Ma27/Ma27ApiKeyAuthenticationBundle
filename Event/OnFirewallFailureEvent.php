<?php

namespace Ma27\ApiKeyAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event to be triggered if the authorization at the firewall fails
 */
class OnFirewallFailureEvent extends Event
{
}
