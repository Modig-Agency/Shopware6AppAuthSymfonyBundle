# Shopware6AppAuthSymfonyBundle
Symfony bundle for Shopware 6 app authentification

## Installation instruction

### in your symfony project:
add to composer.json repositories:
```
"repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Modig-Agency/Shopware6AppAuthSymfonyBundle.git"
        }
    ],
```

in console run ```composer require modig/shopware-6-app-auth-bundle:"^1.0"```

after installation, add to ```config/bundles.php``` if not already added:
```php
return [
    ...
    Modig\ShopwareAppAuthenticationBundle\ShopwareAppAuthenticationBundle::class => ['all' => true],
];
```

add to ```config/packages/modig_shopware_app_authentication.yaml```
```yaml
modig_shopware_app_authentication:
  shop_entity: App\Entity\ShopExample # replace with the path to your shop entity
```
IMPORTANT: 
- Your shop entity must implement ```Modig\ShopwareAppAuthenticationBundle\Entity\ShopInterface```

- Your shop entity repository must implement ```Modig\ShopwareAppAuthenticationBundle\Repository\ShopRepositoryInterface```


add to ``config/routes/modig_shopware_app_authentication.yaml``:
```yaml
modig_shopware_app_authentication:
  resource: '@ShopwareAppAuthenticationBundle/Resources/config/routes.xml'
  prefix: /shopware/app
```

add symfony app url parameter in ``config/services.yaml``.  
Examples:
```yaml
parameters:
  app.url: 'http://nginx_app'
```
or
```yaml
parameters:
  app.url: '%env(APP_URL)%'
```

## Usage
Available URLs defined in ``Modig\ShopwareAppAuthenticationBundle\Controller``
- /shopware/app/registration
- /shopware/app/activated
- /shopware/app/deactivated
- /shopware/app/deleted