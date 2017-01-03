Basic usage
-----------

In order to handle the authentication, you need to provide a model that implements *Symfony\Component\Security\Core\User\UserInterface*
since this interface is required by Symfony's Security component.

Now your user must contain a `login` (may be username, email or something else) and `password` property
that will be used by Doctrine in order to find and validate the user.
Another `apiKey` property is needed to store the generated API key.

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
  private $lastAction; // only necessary when using API key cleanup feature, see the section about the API key purger
}
```

In order to protect some routes and ensure that they can be only used with an API key, you have to configure your *security.yml*:

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
This API key authenticator adopts the concept of stateless authenticators implemented in Symfony's Security component
which don't rely on a session storage, but run the authentication always when a URL protected by the firewall
is matched, so the API key will always be validated.

Say you have a route */restricted/resource.json*.
In order to access it without getting a *401* error, you have to provide a certain header which name is configured in ``key_header`` that contains the api key.
The default value of this header is ``X-API-KEY``, but can be changed in the config (refer to the [configuration reference](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/Resources/doc/configuration.md) for further information).

### [Next: Login API](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/Resources/doc/login-api.md)
