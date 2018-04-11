Ma27ApiKeyAuthenticationBundle
==============================

[![Build Status](https://travis-ci.org/Ma27/Ma27ApiKeyAuthenticationBundle.svg?branch=master)](https://travis-ci.org/Ma27/Ma27ApiKeyAuthenticationBundle)
[![Code Coverage](https://scrutinizer-ci.com/g/Ma27/Ma27ApiKeyAuthenticationBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Ma27/Ma27ApiKeyAuthenticationBundle/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Ma27/Ma27ApiKeyAuthenticationBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ma27/Ma27ApiKeyAuthenticationBundle/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3d8e18e2-06b5-407d-9c6a-47245882d8d8/mini.png)](https://insight.sensiolabs.com/projects/3d8e18e2-06b5-407d-9c6a-47245882d8d8)
[![Latest Stable Version](https://poser.pugx.org/ma27/api-key-authentication-bundle/v/stable)](https://packagist.org/packages/ma27/api-key-authentication-bundle)
[![License](https://poser.pugx.org/ma27/api-key-authentication-bundle/license)](https://packagist.org/packages/ma27/api-key-authentication-bundle)

Symfony Bundle which provides an approach to authenticate users with API tokens.

### Current status

Unfortunately [@Ma27](https://github.com/Ma27/) doesn't have sufficient time to to keep this project running. Just open an issue if you're interested in helping out.

1) About
--------

This bundle applies the concept of a stateless user authenticator as described in the [Symfony CookBook](http://symfony.com/doc/current/cookbook/security/api_key_authentication.html).

Furthermore it contains some extra features:

- RESTful actions in order to generate its own api key and for the logout
- Generator for safe API keys
- Abstract model based on `doctrine/common` (it is possible to use any Doctrine implementation like `doctrine/mongodb-odm` or `doctrine/phpcr-odm`)
- Powerful event system (it is possible to hook into all important processes)
- Implementation to manage password hashing using different strategies
- Command which purges API keys that weren't used for a certain time

2) Documentation
----------------

### Basic introduction

To get a basic idea of how this bundle works, please refer to the following `medium.com` blogposts:

- [Authenticate users with API keys using Symfony and Doctrine](https://medium.com/@_Ma27_/authenticate-users-with-api-keys-using-symfony-and-doctrine-b2270752261a#.it9rtcrq7)
- [How to block users with the Ma27ApiKeyAuthenticationBundle](https://medium.com/@_Ma27_/how-to-block-users-with-the-ma27apikeyauthenticationbundle-5e71dc087b7d#.adfp9rpfn)

### Official documentation

In order to read the official documentation, please refer to the [Resources/doc/index.md](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/Resources/doc/index.md) file.

3) Support and BC promise
-------------------------

For changes in newly release versions please review the [CHANGELOG.md](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/CHANGELOG.md).
To get more information about how to contribute please refer to the [CONTRIBUTING.md](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/.github/CONTRIBUTING.md).

### 1.2.x

Version `1.2` is the latest `1.x` release and will experience support for one year after the final `2.0.0` release came out.
The `1.2` version still supports a lot of legacy PHP versions (`hhvm` and PHP from `5.3.9`).

### 2.x

Version `2.0` dropped support for each PHP and HHVM versions except PHP 7.1.
The support Symfony versions are `2.8` and all `3.x` versions.

### Handling BC breaks

The ancient, unsupported development versions (all `0.x` versions) were experimental development versions and contained BC breaks.

From the `1.x` versions on backward compatibility is provided when jumping between minor releases.
This rule doesn't apply to internals of the codebase (these internals are marked with the `@internal` annotation or have a `private` visibility modifier).
