<?php

namespace Ma27\ApiKeyAuthenticationBundle;

/**
 * Class which contains all event names
 */
final class Ma27ApiKeyAuthenticationEvents
{
    const AUTHENTICATION = 'ma27_api_key_authentication.authentication';
    const LOGOUT = 'ma27_api_key_authentication.logout';
    const BEFORE_CLEANUP = 'ma27_api_key_authentication.session_cleanup.before';
    const AFTER_CLEANUP = 'ma27_api_key_authentication.session_cleanup.after';
    const CREDENTIAL_FAILURE = 'ma27_api_key_authentication.authentication.credential_failure';
    const FIREWALL_FAILURE = 'ma27_api_key_authentication.authorization.firewall.failure';
    const FIREWALL_LOGIN = 'ma27_api_key_authentication.authorization.firewall.login';
}
