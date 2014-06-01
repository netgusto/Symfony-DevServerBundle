# Development server console command with subdomain support, and automatic assets compilation for Symfony 2

## Install

In `composer.json`:

```json
"require": {
    "netgusto/devserver-bundle": "dev-master"
}
```

In `app/AppKernel.php`:

```php
$bundles = array(
    # [...]
    new Netgusto\DevServerBundle\NetgustoDevServerBundle(),
    # [...]
);
```

## Use

```bash
php app/console ng:server
```