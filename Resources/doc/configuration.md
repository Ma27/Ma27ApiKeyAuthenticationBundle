Configuration
-------------

### Configuration reference

Here you can see the default configuration of the API key bundle.

``` yaml
# Default configuration for "Ma27ApiKeyAuthenticationBundle"
ma27_api_key_authentication:
    user:
        api_key_length:       200
        object_manager:       ~ # Required
        model_name:           AppBundle\Entity\User
        password:
            strategy:             php55
        metadata_cache:       false
    api_key_purge:
        enabled:              false
        last_action_listener:
            enabled:              true
        outdated_rule:        '-5 days'
    services:
        auth_handler:         null
        key_factory:          null
    key_header:           X-API-KEY
    response:
        api_key_property:     apiKey
        error_property:       message
```

### Explanations

#### `user` section

- The `api_key_length` value is the length represents the keylength of the API key.
  This value should be an even number since it the generator uses `bin2hex` to normalize the API key before saving.

- The `object_manager` is the service name of the `ObjectManager` implemented by any Doctrine implementation.

- The `model_name` is the FQCN of the user entity to be managed.

- The `password.strategy` is the alias of the hashing strategy to be used during the authentication.

- The `metadata_cache` is a flag whether or not to cache the evaluated Metadata.

#### `api_key_purge` section

- The `enabled` is a flag whether or not to enable the API key cleanup tool.

- The `last_action_listener.enabled` is a flag whether or not to enable a listener which updates the `lastAction`
  value in a user entity during the authentication process.

- The `outdated_rule` is a PHP/datetime rule which determines how old the latest action of a user must be until the Session cleanup
  removes the API key from a user.

#### `services` section

- The `auth_handler` is optional and may contain a service ID which will be used instead of the actual `ApiKeyAuthenticationHandler`.

- The `key_factory` is optional and may contain a service ID which will be used instead of the actual `KeyFactory`.

#### `key_header` section

The `key_header` is a simple, scalar value which contains the name of the API key header. Default is `X-API-KEY`.

#### `response` section

- The `api_key_property` is the name of the property in the JSON response which contains the requested API key. Default is `apiKey`.

- The `error_property` is the name of the property in the JSON response which contains the error message from the API key authentication. Default is `message`.

### [Next: Basic usage](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/Resources/doc/basic-usage.md)
