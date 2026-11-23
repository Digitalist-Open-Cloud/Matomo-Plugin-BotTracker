# Running tests

Run these from the root of a Matomo checkout that has the plugin in `plugins/BotTracker`.

```bash
./console tests:run BotTracker
./vendor/bin/phpcs -q -s --standard=plugins/BotTracker/tests/phpcs.xml plugins/BotTracker
docker run --rm -v "$PWD":/app -w /app ghcr.io/phpstan/phpstan:1-php8.4 analyse -c plugins/BotTracker/phpstan.neon --memory-limit=2G
```
