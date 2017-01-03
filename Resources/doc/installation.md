Installation
------------

This bundle can be simply added using composer:

``` json
{
    "require": {
        "ma27/api-key-authentication-bundle": "^2.0"
    },
    "minimum-stability": "dev"
}
```

Now you simply need to enable the bundle:

``` php
class AppKernel extends Kernel
{
    // ...
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Ma27\ApiKeyAuthenticationBundle\Ma27ApiKeyAuthenticationBundle(),
        ];

        // ...

        return $bundles;
    }
}
```

### [Next: Configuration](https://github.com/Ma27/Ma27ApiKeyAuthenticationBundle/blob/master/Resources/doc/configuration.md)
