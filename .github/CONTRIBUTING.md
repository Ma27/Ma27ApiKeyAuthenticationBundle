## Contribution Guidelines

### How to contribute

- Open an issue to discuss about the change (if the change is bigger and might cause several BC breaks)
- Create a fork of the project
- Create a new local branch called *topic_<name_of_your_branch>*
- Open a PR with the fixes (Pull Request)

### Development Environment

There's a simple vagrant box that can be used when contributing:

``` shell
vagrant up
vagrant ssh
cd /vagrant/auth-bundle
php ./Tests/Resources/app/console.php doctrine:schema:create
```

### Functional Tests

The functional tests will be executed with the rest of the test suite
and require MySQL and the Doctrine ORM (which will be installed when using vagrant).
