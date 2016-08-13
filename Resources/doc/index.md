Ma27ApiKeyAuthenticationBundle
==============================

Table of contents
-----------------

- [Installation](#1-installation)
- [Configuration](#2-configuration)
- [Basic usage](#3-basic-usage)
- [Login API](#4-login-api)
- [Password Hasher](#5-password-hasher)
- [Event system](#6-event-system)
- [API Key Purger](#7-api-key-purger)
- [Overriding services](#8-overriding-services)
- [Override the response](#9-override-the-response)


1) Installation
---------------

This bundle can be simply added using composer:

``` json
{
    "require": {
        "ma27/api-key-authentication-bundle": "^1.2"
    }
}
```

Now you simply need to enable the bundle:

``` php
class AppKernel extends Kernel
{
    // ...
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Ma27\ApiKeyAuthenticationBundle\Ma27ApiKeyAuthenticationBundle(),
        );
        
        // ...
        
        return $bundles
    }
}
```

2) Configuration
----------------

Here you can see the default configuration of the API key bundle.

``` yaml
# Default configuration for "Ma27ApiKeyAuthenticationBundle"
ma27_api_key_authentication:
    user:
        api_key_length:       200
        object_manager:       ~ # Required
        model_name:           AppBundle\Entity\User
        password:
            strategy:             ~ # Required
    api_key_purge:
        enabled:              false
        log_state:            false
        logger_service:       logger
        last_action_listener:
            enabled:              true
    services:
        auth_handler:         null
        key_factory:          null
        password_hasher:      null
    key_header:           X-API-KEY
    response:
        api_key_property:     apiKey
        error_property:       message
```

3) Basic usage
--------------

In order to handle the authentication, you need to provide a model that implements *Symfony\Component\Security\Core\User\UserInterface* as this interfaces is required for Symfony
as it needs this interface for the authentication and authorization process.

Now your user must contain a "login" (may be username or email or something else) and "password" property
that will be used by doctrine in order to find and validate the user.

After that you have to provide the doctrine manager service. When using the *doctrine-orm* package, it is usually the service __doctrine.orm.default_entity_manager__.
You also have to provide the FQCN of the user entity.

This must be done in the configuration:

``` yaml
    # ...
    object_manager: object_manager_service_name
    model_name: AppBundle\Entity\User
```

In order to tell this bundle which properties should be used for login, password and api key, annotations must be attached at the model:

``` php
namespace AppBundle\Entity;

use Ma27\ApiKeyAuthenticationBundle\Annotation as Auth;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface {
  /** @Auth\Login */
  private $usernameOrEmail;
  /** @Auth\Password */
  private $password;
  /** @Auth\ApiKey */
  private $apiKey; // for authentication using the api key
  /** @Auth\LastAction */
  private $lastAction; // only necessary when using purger feature, see the section about the API key purger
}
```

In order to protect some routes using the api key authenticator, you have to configure your *security.yml*:

``` yaml
# security.yml
security:
    providers:
        in_memory:
            memory: ~
    firewalls:
        default:
            pattern:   ^/restricted
            stateless: true
            simple_preauth:
                authenticator: ma27_api_key_authentication.security.authenticator
```

We don't need ay user provider, so we just adjust an empty memory provider.

In that configuration we protected all routes with the url prefix */restricted*.
This API key authenticator adopts the concept of stateless authenticators which don't rely on a session storage, but run the authentication always when a URL protected by the firewall
is accessed, so the API key will always be validated.

Say you have the route */restricted/resource.json*.
In order to access it without getting a *401* error, you have to provide a certain header which name is configured in ``key_header`` that contains the api key.
The default value of this header is ``X-API-KEY``, but can be changed in the config (refer to the [configuration reference](#2-configuration) for further information).

4) Login API
------------

With your configuration it is possible now to protect certain routes matched by the firewall. But how to get the api key?

At first we need to enable the routes of the login api:

``` yaml
# routing.yml
ma27_api_key_authentication:
    resource: "@Ma27ApiKeyAuthenticationBundle/Resources/config/routing/routing.yml"
    prefix: /
```

In order to request the api key, you have to send a *POST* request to the route */api-key.json*.
You have to adjust all model parameters provided in the bundle configuration (e.g. username and password and its values).

The response will look like this:

``` json
{
    "apiKey": "a very long api key"
}
```

You just have to call the same route with the *DELETE* method in order to remove the api key and "logout".
__NOTE__: you have to send the api key as header when calling the logout route.

5) Password hasher
------------------

As there are many password hashing algorithms, I decided to use the [Strategy Pattern](https://en.wikipedia.org/wiki/Strategy_pattern) for the password hasing api.

Every hasher must implement the interface *Ma27\ApiKeyAuthenticationBundle\Model\Password\PasswordHasherInterface* that contains the methods *generateHash* and *compareHash*.

Currently are the following algorithms available:

- php55 (php's internal hashing api, `passwod_hash`)
- phpass
- crypt

You can enable them like this:

``` yaml
    # ...
    password:
        strategy: "your hasing strategy"
```

You have to replace the "strategy" value with one of the above listed hashers

### 5.1) Custom hasher

Custom hashers are easy to create:

1.: create your own hashing class:

``` php
namespace AppBundle\Hasher;

use Ma27\ApiKeyAuthenticationBundle\Model\Password\PasswordHasherInterface;

class CustomHasher implements PasswordHasherInterface
{
    public function generateHash($password)
    {
        // build the hash by a raw password
    }
    public function compareWith($password, $raw)
    {
        // compares the hash $password with a raw string
    }
}
```

2.: register it in the container:

``` yaml
services:
    app.custom_hasher:
        class: AppBundle\Hasher\CustomHasher
        tags:
            - { name: ma27_api_key_authentication.password_hashing_service, alias: custom }
```

3.: enable it in the config:

``` php
ma27_api_key_authentication:
    password:
        strategy: custom # value of the `alias` parameter in the tag config
```

6) Event system
---------------

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

__NOTE:__ there's a difference between the `credential_failure` and `credential_exception_thrown` event: the `credential_failure` event is triggered if the login or password value is invalid (thrown and triggered by the `ApiKeyAuthenticationHandler`). The other on is triggered whenever a `CredentialException` is thrown during the login.

7) API key purger
-----------------

The purger can be really useful when removing older api keys.
When the latest activation is 5 days ago, the user can be removed using the api key purger:

``` yaml
    # ...
    api_key_purge:
        enabled: true
        log_state: false
        logger_service: logger
        last_action_listener:
            enabled:              true
```

Here you have to adjust a property of the domain model that shows you the latest activation as timestamp.
If you'd like to log the progress of the removal, you have to set the *log_state* value to *true*, but you have to provide a service called *logger* (e.g. the logger of the MonologBundle).

The *logger_service* is optional and is the service id of the logger (default value is *logger*).

The command can be used over the cli:

    php app/console ma27:auth:session-cleanup

It is recommended to use this as a cronjob:

    crontab -e
    @midnight /usr/bin/php /path/to/application/app/console ma27:auth:session-cleanup

__Please keep in mind that the logger support is deprecated. The API key purger provides a lot of events that can be used to implement a custom logger.__

The `last_action_listener` updates the last action property after the API key request and whenever a user authenticates on a route protected by a firewall using the `ApiKeyAuthenticator`.
It's enabled by the default and can be disabled by setting `last_action_listener.enabled` to `false`.

8) Overriding services
----------------------

It is possible to override the services, too.

The overridable services are:

- auth_handler (Ma27\ApiKeyAuthenticationBundle\Model\Login\ApiKey\ApiKeyAuthenticationHandler)
- key_factory (Ma27\ApiKeyAuthenticationBundle\Model\Key\KeyFactory)
- password_hasher (Ma27\ApiKeyAuthenticationBundle\Model\Password\PasswordHasherInterface)

There's a service section in the bundle config that can be used in order to exchange these services.

Please keep in mind that `password_hasher` is obsolete as you can use custom password hashers.

9) Override the response
------------------------

For certain use-cases it is necessary to override the response of the login.
This can be done by using the ``AssembleResponseEvent``:

__NOTE: this feature is only available for the login route as the logout returns a 204, so no customization is needed right now.__

``` php
use Ma27\ApiKeyAuthenticationBundle\Ma27ApiKeyAuthenticationEvents;
use Ma27\ApiKeyAuthenticationBundle\Event\AssembleResponseEvent;

class CustomResponseListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(Ma27ApiKeyAuthenticationEvents::ASSEMBLE_RESPONSE => 'onResponseCreation');
    }

    public function onResponseCreation(AssembleResponseEvent $event)
    {
        if ($event->isSuccess()) {
            $user = $event->getUser();
            // do sth. with $user

            $event->setResponse(array(/* response data */));
            // propagation must be stopped to avoid calling the
            // default response listener which would override everything.
            $event->stopPropagation();
            return;
        }

        // handle the error event
        $exception = $event->getException();
        $event->setResponse(new JsonResponse(array(/* response data */)));
    }
}
```

Now this subscriber must be registered and tagged as `kernel.event_subscriber` and you can override this response.
