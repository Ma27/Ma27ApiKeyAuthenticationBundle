Login API
---------

With your configuration it is possible now to protect certain routes matched by the firewall. But how to request a generated the api key?

At first we need to enable the routes provided by this bundle in the `routing.yml`:

``` yaml
# routing.yml
ma27_api_key_authentication:
    resource: "@Ma27ApiKeyAuthenticationBundle/Resources/config/routing/routing.yml"
    prefix: /
```

In order to request the api key, you have to send a *POST* request to the route `/api-key.json`.
The request needs a `login` and `password` property that will be used by the internal authentication implementation to load a user and create an API key for him.

The response will look like this:

``` json
{
    "apiKey": "a very long api key"
}
```

You just have to call the same route with the *DELETE* method with the API key in the header to remove the api key and "logout".

#### [Next: Password Hasher](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/Resources/doc/password-hasher.md)
