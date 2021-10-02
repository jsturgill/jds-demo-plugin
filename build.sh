#!/bin/bash

# clear previous build (if present)
rm -r build || echo 'no previous build directory found'

# install prod-only dependencies
./dev-utils/composer.phar --working-dir=jds-demo-plugin install --no-dev
# run php-scoper
./dev-utils/scoper/vendor/bin/php-scoper add-prefix --force -v
# rebuild autoloader in prod build directory
./dev-utils/composer.phar --working-dir=build dump-autoload --classmap-authoritative
# restore full dependencies in local dev direcory
./dev-utils/composer.phar --working-dir=jds-demo-plugin install
# fix build output after php-scoper revisions
./dev-utils/php-cs-fixer/vendor/bin/php-cs-fixer fix
# ensure log and cache directories exist
mkdir -p build/logs
mkdir -p build/cache
