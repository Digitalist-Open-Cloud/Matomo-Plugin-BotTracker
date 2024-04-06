# Running tests

## PHPStan

Go to BotTracker plugin dir:

```bash
composer install
```

Go to Matomo root (/var/www/html usually) run:

```bash
/var/www/html/plugins/BotTracker/vendor/bin/phpstan analyze -c /var/www/html/plugins/BotTracker/tests/phpstan.neon --level=1 /var/www/html/plugins/BotTracker
```

## PHPCS

Go to BotTracker plugin dir:

```bash
composer install
```

Run PHP Codesniffer

```bash
vendor/bin/phpcs --ignore=*/vendor/*,Updates/*,tests/*  --standard=PSR2 .
vendor/bin/phpcs --ignore=*/vendor/*,Updates/*,tests/*  --standard=PSR12 .
```

## Unit tests

Go to plugins/BotTracker

```bash
composer install
````

Got to Matomo web root folder (normally `/var/www/html`).

Set up test environment:

(`http_host` is depending on your testing environment, if the host is matomo.loc, set that instead, also same for database etc.)

```bash
./console development:enable
./console config:set --section=tests --key=http_host --value=matomo.loc
./console config:set --section=tests --key=request_uri --value=/
./console config:set --section=database_tests --key=host --value=db
./console config:set --section=database_tests --key=username --value=root
./console config:set --section=database_tests --key=password --value=root
./console config:set --section=database_tests --key=dbname --value=matomo_test
./console config:set --section=database_tests --key=tables_prefix --value=""
```

### Run tests

```bash
/var/www/html/plugins/BotTracker/vendor/bin/phpunit -c plugins/BotTracker/tests/phpunit.xml
```

