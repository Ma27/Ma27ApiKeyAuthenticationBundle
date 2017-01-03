Password hasher
---------------

As there are many password hashing algorithms, I decided to use the [Strategy Pattern](https://en.wikipedia.org/wiki/Strategy_pattern) for the password hasing api.

Every hasher must implement the interface *Ma27\ApiKeyAuthenticationBundle\Service\Password\PasswordHasherInterface* that contains the methods *generateHash* and *compareHash*.

Currently are the following algorithms available:

- `php55` (php's internal hashing api, `passwod_hash`)
- `phpass`
- `crypt`

You can enable them like this:

``` yaml
    # ...
    password:
        strategy: "your hasing strategy"
```

You have to replace the "strategy" value with one of the above listed hashers

### Custom hasher

Custom hashers are easy to create:

#### 1. create your own hashing class:

``` php
namespace AppBundle\Hasher;

use Ma27\ApiKeyAuthenticationBundle\Service\Password\PasswordHasherInterface;

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

#### 2. register it in the container:

``` yaml
services:
    app.custom_hasher:
        class: AppBundle\Hasher\CustomHasher
        tags:
            - { name: ma27_api_key_authentication.password_hashing_service, alias: custom }
```

#### 3. enable it in the config:

``` php
ma27_api_key_authentication:
    password:
        strategy: custom # value of the `alias` parameter in the tag config
```

### [Next: Event system](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/Resources/doc/event-system.md)
