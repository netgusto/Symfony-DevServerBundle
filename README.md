# Development server console command with custom tasks support

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

## Configure

In `app/config.yml`:

```yaml
netgusto_dev_server:
    tasks:
        - { command: php app/console server:run 0.0.0.0:8000 }
        - { command: php app/console assetic:dump --watch }
        - { command: ember serve, path: web/apps/calclient }
```

## Use

```bash
php app/console server:dev
```