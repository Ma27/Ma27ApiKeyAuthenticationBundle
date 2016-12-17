# Changelog for 1.x version

## 1.2.1

- [bug] `33e4bfe` invalid import leads to issues when dumping the DI graph

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
