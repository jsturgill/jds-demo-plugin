#!/bin/bash

rm -r build

./dev-utils/composer.phar --working-dir=jds-demo-plugin install --no-dev
./dev-utils/scoper/vendor/bin/php-scoper add-prefix --force -v
mkdir build/logs
./dev-utils/composer.phar --working-dir=build dump-autoload --classmap-authoritative
./dev-utils/composer.phar --working-dir=jds-demo-plugin install
