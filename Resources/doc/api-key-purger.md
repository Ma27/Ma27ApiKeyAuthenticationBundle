API key purger
--------------

The purger can be really useful when removing API keys of users that weren't active for a longer time.

When the latest action of a user is 5 days ago, the user can be removed using the api key purger:

``` yaml
    # ...
    api_key_purge:
        enabled: true
        last_action_listener:
            enabled: true
```

The `last_action_listener` enables a listener which modifies the last action after the login and whenever the user is authenticated at the firewall.

In order to change the rule after which time to expire an API key, the PHP/datetime rule can be changed:

``` yaml
    # ...
    api_key_purge:
        enabled: true
        outdated_rule: '-4 days'
```

In order to detect the latest activation, you have to add another property to the entity that shows you the latest activation as timestamp.
(See [Basic Usage](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/Resources/doc/basic-usage.md) for more information).

The command can be used over the cli:

```
bin/console ma27:auth:session-cleanup
```

But it's recommended to use this as a cronjob:

```
crontab -e
@midnight /usr/bin/php /path/to/application/app/console ma27:auth:session-cleanup
```

#### [Next: Overriding services](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/Resources/doc/overriding-services.md)
