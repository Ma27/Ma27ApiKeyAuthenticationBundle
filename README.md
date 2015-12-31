Ma27ApiKeyAuthenticationBundle
==============================

[![Build Status](https://travis-ci.org/Ma27/Ma27ApiKeyAuthenticationBundle.svg?branch=master)](https://travis-ci.org/Ma27/Ma27ApiKeyAuthenticationBundle)
[![Code Coverage](https://scrutinizer-ci.com/g/Ma27/Ma27ApiKeyAuthenticationBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Ma27/Ma27ApiKeyAuthenticationBundle/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Ma27/Ma27ApiKeyAuthenticationBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ma27/Ma27ApiKeyAuthenticationBundle/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3d8e18e2-06b5-407d-9c6a-47245882d8d8/mini.png)](https://insight.sensiolabs.com/projects/3d8e18e2-06b5-407d-9c6a-47245882d8d8)
[![Latest Stable Version](https://poser.pugx.org/ma27/api-key-authentication-bundle/v/stable)](https://packagist.org/packages/ma27/api-key-authentication-bundle)
[![Latest Unstable Version](https://poser.pugx.org/ma27/api-key-authentication-bundle/v/unstable)](https://packagist.org/packages/ma27/api-key-authentication-bundle)
[![License](https://poser.pugx.org/ma27/api-key-authentication-bundle/license)](https://packagist.org/packages/ma27/api-key-authentication-bundle)
[![PHP 7 ready](http://php7ready.timesplinter.ch/Ma27/Ma27ApiKeyAuthenticationBundle/badge.svg)](https://travis-ci.org/Ma27/Ma27ApiKeyAuthenticationBundle)

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

3) Symfony 3.0
--------------

All versions 0.1.x versions support all Symfony 2.x versions.
This branch will be still maintained.

The 0.2 versions support all Symfony 3.x versions including Symfony 2.8.

4) Contributing
---------------

####There's a short instruction for contributing:

- Fork the project
- Create a new local branch called *topic_<name_of_your_branch>*
- Open a PR (Pull Request)

####Devtools

There's a simple vagrant box that can be used when contributing:

    vagrant up
    vagrant ssh
    cd /vagrant/auth-bundle

####Functional Tests

The functional tests will be executed with the rest of the test suite and require mysql (which will be installed when using vagrant)

5) LICENSE
----------

MIT.
See the [Resources/meta/LICENSE](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/Resources/meta/LICENSE) file for more information

6) Support
-----------

This software supports all php versions from php-5.3 up to php-7-nightly.
The supported symfony version are 2.8 and 3.x

7) BC promise
-------------

From the *1.x* versions on (so no 0.x versions) backward compat is provided when jumping between minor releases except you try to do stuff with internals such as classes marked with "@internal" or private properties/methods.
