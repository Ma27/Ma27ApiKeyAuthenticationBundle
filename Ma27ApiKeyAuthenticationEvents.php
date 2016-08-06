<?php

namespace Ma27\ApiKeyAuthenticationBundle;

/**
 * Class which contains all event names.
 */
final class Ma27ApiKeyAuthenticationEvents
{
    /**
     * Event that will be triggered during the login process
     * When the user is loaded, this event can be used in order to write custom login rules.
     *
     * @var string
     */
    const AUTHENTICATION = 'ma27_api_key_authentication.authentication';

    /**
     * Event that will be triggered before the removal of the api key.
     *
     * @var string
     */
    const LOGOUT = 'ma27_api_key_authentication.logout';

    /**
     * Event that will be triggered before the api key cleanup starts.
     *
     * @var string
     */
    const BEFORE_CLEANUP = 'ma27_api_key_authentication.session_cleanup.before';

    /**
     * Event that will be triggered when all outdated users were detected and can be commited.
     *
     * @var string
     */
    const CLEANUP_SUCCESS = 'ma27_api_key_authentication.session_cleanup.success';

    /**
     * Event to be triggered if the login fails.
     *
     * @var string
     */
    const CREDENTIAL_FAILURE = 'ma27_api_key_authentication.credential_failure';

    /**
     * Event to be triggered if a `CredentialException` has been thrown.
     *
     * @var string
     */
    const CREDENTIAL_EXCEPTION_THROWN = 'ma27_api_key_authentication.credential_exception_thrown';

    /**
     * Event to be triggered if the firewall (pre authenticator) refuses access.
     *
     * @var string
     */
    const FIREWALL_FAILURE = 'ma27_api_key_authentication.authorization.firewall.failure';

    /**
     * Event to be triggered when the firewall authorization logic starts.
     *
     * @var string
     */
    const FIREWALL_LOGIN = 'ma27_api_key_authentication.authorization.firewall.login';

    /**
     * Event to be triggered if the cleanup failed.
     *
     * @var string
     */
    const CLEANUP_ERROR = 'ma27_api_key_authentication.cleanup.error';

    /**
     * Event to be triggered if all changed objects were commited and the whole cleanup is complete.
     *
     * @var string
     */
    const AFTER_CLEANUP = 'ma27_api_key_authentication.cleanup.complete';

    /**
     * Event to be triggered to assemble the http response of the API key request.
     *
     * @var string
     */
    const ASSEMBLE_RESPONSE = 'ma27_api_key_authentication.response.assemble';
}
