ApiKeyAuthenticationBundle
==========================

[![Build Status](https://travis-ci.org/Ma27/Ma27ApiKeyAuthenticationBundle.svg?branch=master)](https://travis-ci.org/Ma27/Ma27ApiKeyAuthenticationBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Ma27/Ma27ApiKeyAuthenticationBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ma27/Ma27ApiKeyAuthenticationBundle/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3d8e18e2-06b5-407d-9c6a-47245882d8d8/mini.png)](https://insight.sensiolabs.com/projects/3d8e18e2-06b5-407d-9c6a-47245882d8d8)

This bundle provides a way of restful authentication using api keys.

1) About
--------

This bundle applies the concept of a stateless user authenticator as described in the [Symfony CookBook](http://symfony.com/doc/current/cookbook/security/api_key_authentication.html).
This bundle has some extra features:

- RESTful actions in order to get the api key and for the logout
- API key generator
- abstract model (it is possible to use any doctrine implementation like Doctrine-ODM-MongoDB or Doctrine-ODM-PHPCR)
- powerful event handling system (it is possible to hook into all important processes)
- strategy for password hashing
- session purger

2) Documentation
----------------

Click [here](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/Resources/doc/index.md) in order to view the current docs.

3) Contributing
---------------

*in progress*

4) LICENSE
----------

MIT. See the [LICENSE](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/LICENSE) file
