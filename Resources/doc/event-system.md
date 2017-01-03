Event system
------------

As it should be possible to hook into all important processes, this bundle uses the symfony dispatcher pretty much.

There are currently the following events in use:

| Event name                                                 | Description                                                                          | Event class                                                            |
| ---------------------------------------------------------- | ------------------------------------------------------------------------------------ | ---------------------------------------------------------------------- |
| ma27_api_key_authentication.authentication                 | This one will be triggered when the user is validated and before the key generation  | Ma27\ApiKeyAuthenticationBundle\Event\OnAuthenticationEvent            |
| ma27_api_key_authentication.logout                         | This one will be triggered when the api key is about to be removed                   | Ma27\ApiKeyAuthenticationBundle\Event\OnLogoutEvent                    |
| ma27_api_key_authentication.session_cleanup.before         | This one will be triggered before the api key cleanup                                | Ma27\ApiKeyAuthenticationBundle\Event\OnBeforeSessionCleanup           |
| ma27_api_key_authentication.session_cleanup.success        | This one will be triggered when the cleanup succeeded                                | Ma27\ApiKeyAuthenticationBundle\Event\OnSuccessfulCleanupEvent         |
| ma27_api_key_authentication.credential_failure             | This one will be triggered if the authentication failed                              | Ma27\ApiKeyAuthenticationBundle\Event\OnInvalidCredentialsEvent        |
| ma27_api_key_authentication.credential_exception_thrown    | This one will be triggered if the `CredentialException` has been thrown              | Ma27\ApiKeyAuthenticationBundle\Event\OnCredentialExceptionThrownEvent |
| ma27_api_key_authentication.authorization.firewall.failure | This one will be triggered if the firewall was unable to authenticate a user         | Ma27\ApiKeyAuthenticationBundle\Event\OnFirewallFailureEvent           |
| ma27_api_key_authentication.authorization.firewall.login   | This one will be triggered if the login on the firewall starts                       | Ma27\ApiKeyAuthenticationBundle\Event\OnFirewallAuthenticationEvent    |
| ma27_api_key_authentication.cleanup.error                  | This one will be triggered if the cleanup failed                                     | Ma27\ApiKeyAuthenticationBundle\Event\OnApiKeyCleanupErrorEvent        |
| ma27_api_key_authentication.cleanup.complete               | This one will be triggered when the cleanup is complete and the changes were flushed | Ma27\ApiKeyAuthenticationBundle\Event\OnAfterCleanupEvent              |

__NOTE:__ there's a difference between the `credential_failure` and `credential_exception_thrown` event: the `credential_failure` event is triggered
if the login or password value is invalid (thrown and triggered by the `ApiKeyAuthenticationHandler`).

The other on is triggered whenever a `CredentialException` is thrown during the login.

#### [Next: API Key Purger](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/Resources/doc/api-key-purger.md)
