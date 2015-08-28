<?php

namespace Ma27\ApiKeyAuthenticationBundle\Event;

/**
 * Class which contains all event names
 */
final class Events
{
    const AUTHENTICATION = 'ma27.auth.authentication';
    const LOGOUT = 'ma27.auth.logout';
    const BEFORE_CLEANUP = 'ma27.auth.session_cleanup.before';
    const AFTER_CLEANUP = 'ma27.auth.session_cleanup.after';
    const CREDENTIAL_FAILURE = 'ma27.auth.authentication.credential_failure';
    const FIREWALL_FAILURE = 'ma27.auth.authorization.firewall.failure';
    const FIREWALL_LOGIN = 'ma27.auth.authorization.firewall.login';
}
