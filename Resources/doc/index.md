Ma27ApiKeyAuthenticationBundle
==============================

Table of contents
--------------------

- Installation
- Configuration
- Basic usage
- Login API
- Password Hasher
- Event system
- API Key Purger
- Overriding services
- Override the response


1) Installation
---------------

This bundle can be simply added using composer:

``` json
{
    "require": {
        "ma27/api-key-authentication-bundle": "^1.0"
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

Here you can see the full configuration of the bundle

``` yaml
# Current configuration for "Ma27ApiKeyAuthenticationBundle"
ma27_api_key_authentication:
    user:
        object_manager: om_service
        model_name: 'AppBundle\\Entity\\User'
        password:
            strategy: crypt,
            phpass_iteration_length: 8
        api_key_length: 200
    api_key_purge:
        enabled: false
        log_state: false
    services:
        auth_handler: null
        key_factory: null
        password_hasher: null
    key_header: X-API-KEY
```

3) Basic usage
--------------

In order to handle the authentication, you need to provide a model that implements *Symfony\Component\Security\Core\UserInterface* as such classes are required for the authentication.

Now your user must contain the "login" (may be username or email or something else) and "password" property
that will be used by doctrine in order to find and validate the user.

Now you have to provide the doctrine manager service. When using the *doctrine-orm* package, it is usually the service __doctrine.orm.default_entity_manager__.
You also have to provide the doctrine model name.

This must be done in the configuration:

``` yaml
    # ...
    object_manager: object_manager_service_name
    model_name: 'AppBundle\\Entity\\User'
```

In order to tell this bundle which properties should be used, annotations must be attached at the model:

``` php
namespace AppBundle\Entity;

use Ma27\ApiKeyAuthenticationBundle\Annotation as Auth;
use Symfony\Component\Security\Core\UserInterface;

class User implements UserInterface {
  /** @Auth\Login */
  private $usernameOrEmail;
  /** @Auth\Password */
  private $password;
  /** @Auth\ApiKey */
  private $apiKey; // for authentication using the api key
  /** @Auth\LastAction */
  private $lastAction; // only necessary when using purger feature
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
It must be stateless since we have to provide the api key all the time and don't use sessions.

Say you have the route */restricted/resource.json*.
In order to access it without getting a *401* error, you have to provide a certain header which name is configured in ``key_header`` that contains the api key.
The default value of this header is ``X-API-KEY``.

4) Login API
------------

With your configuration it is possible now to protect some routes. But how to get the api key?

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

- sha512
- php55 (php's internal hashing api)
- phpass
- crypt

You can enable them like this:

``` yaml
    # ...
    password:
        strategy: "your hasing strategy"
```

You have to replace the "strategy" value with one of the above listed hashers

6) Event system
---------------

As it should be possible to hook into all important processes, this bundle uses the symfony dispatcher pretty much.

There are currently the following events in use:

| Event name                                                 | Description                                                                          | Event class                                                         |
| ---------------------------------------------------------- | ------------------------------------------------------------------------------------ | ------------------------------------------------------------------- |
| ma27_api_key_authentication.authentication                 | This one will be triggered when the user is validated and before the key generation  | Ma27\ApiKeyAuthenticationBundle\Event\OnAuthenticationEvent         |
| ma27_api_key_authentication.logout                         | This one will be triggered when the api key is about to be removed                   | Ma27\ApiKeyAuthenticationBundle\Event\OnLogoutEvent                 |
| ma27_api_key_authentication.session_cleanup.before         | This one will be triggered before the api key cleanup                                | Ma27\ApiKeyAuthenticationBundle\Event\OnBeforeSessionCleanup        |
| ma27_api_key_authentication.session_cleanup.success        | This one will be triggered when the cleanup succeeded                                | Ma27\ApiKeyAuthenticationBundle\Event\OnSuccessfulCleanupEvent      |
| ma27_api_key_authentication.credential_failure             | This one will be triggered if the authentication failed                              | Ma27\ApiKeyAuthenticationBundle\Event\OnInvalidCredentialsEvent     |
| ma27_api_key_authentication.authorization.firewall.failure | This one will be triggered if the firewall was unable to authenticate a user         | Ma27\ApiKeyAuthenticationBundle\Event\OnFirewallFailureEvent        |
| ma27_api_key_authentication.authorization.firewall.login   | This one will be triggered if the login on the firewall starts                       | Ma27\ApiKeyAuthenticationBundle\Event\OnFirewallAuthenticationEvent |
| ma27_api_key_authentication.cleanup.error                  | This one will be triggered if the cleanup failed                                     | Ma27\ApiKeyAuthenticationBundle\Event\OnApiKeyCleanupErrorEvent     |
| ma27_api_key_authentication.cleanup.complete               | This one will be triggered when the cleanup is complete and the changes were flushed | Ma27\ApiKeyAuthenticationBundle\Event\OnAfterCleanupEvent           |

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
```

Here you have to adjust a property of the domain model that shows you the latest activation as timestamp.
If you'd like to log the progress of the removal, you have to set the *log_state* value to *true*, but you have to provide a service called *logger* (e.g. the logger of the MonologBundle).

The *logger_service* is optional and is the service id of the logger (default value is *logger*).

The command can be used over the cli:

    php app/console ma27:auth:session-cleanup

It is recommended to use this as a cronjob:

    crontab -e
    @midnight /usr/bin/php /path/to/application/app/console ma27:auth:session-cleanup

8) Overriding services
----------------------

It is possible to override the services, too.

The overridable services are:

- auth_handler (Ma27\ApiKeyAuthenticationBundle\Model\Login\ApiKey\ApiKeyAuthenticationHandler)
- key_factory (Ma27\ApiKeyAuthenticationBundle\Model\Key\KeyFactory)
- password_hasher (Ma27\ApiKeyAuthenticationBundle\Model\Password\PasswordHasherInterface)

There's a service section in the bundle config that can be used in order to exchange these services.

9) Override the response
------------------------

For certain use-cases it is necessary to override the response.
This can be done by using the ``AssembleResponseEvent``:

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
