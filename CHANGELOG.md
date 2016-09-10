# Changelog for 1.x version

## 2.0.0

- [feature] Made `KeyFactory#doGenerate` protected in order to override only the generation part.

- [minor] The length computation `actual_length / 2` for the random key will be done in the `KeyFactory` instead of the extension.

- [breaking] Removed the configuration option `password.phpass_iteration_length`. Instead the parameter `ma27_api_key_authentication.password_hasher.phpass.iteration_length` should be modified manually.

- [breaking] Removed the configuration option `services.password_hasher`. Create a custom hasher via DI tags instead.

- [breaking] Removed the `sha512` strategy and the `Ma27\ApiKeyAuthenticationBundle\Model\Password\Sha512PasswordHasher` as it didn't provide an appropriate salting strategy which made the whole implementation fragile.

- [minor] Added the `ma27_api_key_authentication.password_hasher.php55.cost`

- [breaking] removed `Ma27\ApiKeyAuthenticationBundle\Security\ApiKeyAuthenticator::API_KEY_HEADER`. The API key header is configurable, so no constant is needed anymore.

- [breaking] removed `Ma27\ApiKeyAuthenticationBundle\Event\AbstractUserEvent::isUserAvailable`. The method is only available in the subclass `Ma27\ApiKeyAuthenticationBundle\Event\OnInvalidCredentialsEvent` and in other events not needed.

- [breaking] removed the configuration option `api_key_purge.log_state` and `api_key_purge.logger`. In order to tackle logger support, please use custom event hooks.

- [feature] `user.password` configuration can be omitted if the `php55` strategy is used.

- [feature] the datetime expression for the session cleanup can be configured with the configuration option `api_key_purge.outdated_rule`.

- [breaking] renamed `Ma27\ApiKeyAuthenticationBundle\Event\AssembleResponseEvent` to `Ma27\ApiKeyAuthenticationBundle\Event\OnAssembleResponseEvent` to keep the naming convention for event objects.

- [breaking] removed the `ma27_api_key_authentication.cleanup.complete` event as it will be called right after the success event and has no real benefit.

- [breaking] moved model classes to a new namespace:
  - `Ma27\ApiKeyAuthenticationBundle\Model\Key\KeyFactory` => `Ma27\ApiKeyAuthenticationBundle\Service\Key\KeyFactory`
  - `Ma27\ApiKeyAuthenticationBundle\Model\Key\KeyFactoryInterface` => `Ma27\ApiKeyAuthenticationBundle\Service\Key\KeyFactoryInterface`
  - `Ma27\ApiKeyAuthenticationBundle\Model\Login\ApiToken\ApiKeyAuthenticationHandler` => `Ma27\ApiKeyAuthenticationBundle\Service\Auth\ApiKeyAuthenticationBundle`
  - `Ma27\ApiKeyAuthenticationBundle\Model\Login\AuthenticationHandlerInterface` => `Ma27\ApiKeyAuthenticationBundle\Service\Auth\AuthenticationHandlerInterface`
  - `Ma27\ApiKeyAuthenticationBundle\Model\User\ClassMetadata` => `Ma27\ApiKeyAuthenticationBundle\Service\Mapping\ClassMetadata`
  - `Ma27\ApiKeyAuthenticationBundle\Model\User\ModelConfigurationDriverInterface` => `Ma27\ApiKeyAuthenticationBundle\Service\Mapping\ModelConfigurationDriverInterface`
  - `Ma27\ApiKeyAuthenticationBundle\Model\Password\PasswordHasherInterface` => `Ma27\ApiKeyAuthenticationBundle\Service\Password\PasswordHasherInterface`
  - `Ma27\ApiKeyAuthenticationBundle\Model\Password\CryptPasswordHasher` => `Ma27\ApiKeyAuthenticationBundle\Service\Password\CryptPasswordHasher`
  - `Ma27\ApiKeyAuthenticationBundle\Model\Password\PHPassHasher` => `Ma27\ApiKeyAuthenticationBundle\Service\Password\PHPassHasher`
  - `Ma27\ApiKeyAuthenticationBundle\Model\Password\PhpPasswordHasher` => `Ma27\ApiKeyAuthenticationBundle\Service\Password\PHPPasswordHasher`

- [feature] added `user.metadata_cache` option which can be used to enable a cache for the metadata.
  (__NOTE:__ certain implementation details of the metadata API were changed, but not listed as the whole metadata API is private and tagged with the `@internal` tag)

## 1.2.0

- [feature] made hashing services configurable: (#33)
  - created tag `ma27_api_key_authentication.password_hasher` which allows to create custom services and use them via the configuration
  - no BC breaks (all currently existing hashers can be used by the same config)

- [bug] ensure that no api key is generated before a new one will be created (#48)

- [feature] added `response.api_key_property` and `response.error_property` to the configuration to keep the response configurable (#45)

- [minor] deprecated `api_key_purge.logger_service`, logger support will be removed in 2.0 (#50)

- [feature] added a listener which updates the `last_action` during the firewall login and after the api key request (#55)

- [feature] added an event `ma27_api_key_authentication.credential_exception_thrown` that will be always triggered when a `CredentialException` is thrown (#56)

- [minor] deprecated `services.password_hasher`, instead a custom password hasher should be created.

## 1.1.0

- [feature] made api_key header configurable: (#32)
  - added option `key_header` to make the header name mutable.
  - deprecated the const `Ma27\ApiKeyAuthenticationBundle\Security\ApiKeyAuthenticator::API_KEY_HEADER`.

- [minor] deprecated `Ma27\ApiKeyAuthenticationBundle\Event\AbstractUserEvent#isUserAvailable`: (#36)
  - the method has been moved to `Ma27\ApiKeyAuthenticationBundle\Event\OnInvalidCredentialsEvent` since it is only needed in this subclass of the base event class.
  - the old method can be used, but triggers is declared as `deprecated`.

- [minor] declared parts of the annotation parser implementation as `@internal` as they aren't part of the public API and shouldn't be used anywhere.

- [minor] added a `AssembleResponseEvent` to improve the creation of responses for the API key request (#46)

## 1.0.1

- [bug] extract credentials properly from an authentication request even if they're empty. (#35)
- [docs] added github contribution templates.
