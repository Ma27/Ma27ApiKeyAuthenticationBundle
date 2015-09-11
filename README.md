ApiKeyAuthenticationBundle
==========================

[![Build Status](https://travis-ci.org/Ma27/Ma27ApiKeyAuthenticationBundle.svg?branch=master)](https://travis-ci.org/Ma27/Ma27ApiKeyAuthenticationBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Ma27/Ma27ApiKeyAuthenticationBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ma27/Ma27ApiKeyAuthenticationBundle/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3d8e18e2-06b5-407d-9c6a-47245882d8d8/mini.png)](https://insight.sensiolabs.com/projects/3d8e18e2-06b5-407d-9c6a-47245882d8d8)
[![Latest Stable Version](https://poser.pugx.org/ma27/api-key-authentication-bundle/v/stable)](https://packagist.org/packages/ma27/api-key-authentication-bundle)
[![Latest Unstable Version](https://poser.pugx.org/ma27/api-key-authentication-bundle/v/unstable)](https://packagist.org/packages/ma27/api-key-authentication-bundle)
[![License](https://poser.pugx.org/ma27/api-key-authentication-bundle/license)](https://packagist.org/packages/ma27/api-key-authentication-bundle)

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

__Functional tests are currently not available, but under construction, so this section won't work__

4) LICENSE
----------

MIT.
See the [Resources/meta/LICENSE](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/Resources/meta/LICENSE) file for more information

5) Symfony 2.3 Support
----------------------

Symfony 2.3 doesn't support the *SimplePreAuthenticatorInterface* since it has been added in symfony 2.4.

There's a backport (which is in use for the sf2.3 travis builds btw) that includes this feature in symfony 2.3: [giosh94mhz/simple-pre-authenticator-bundle](https://packagist.org/packages/giosh94mhz/simple-pre-authenticator-bundle)
