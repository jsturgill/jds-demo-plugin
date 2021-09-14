# Demo Plugin

Below are instructions on how to get the dev environment up and running.

The commands were tested under Windows git bash. It should be straightforward for you
to adjust as necessary for your environment.

Note: Docker compose is required, as is PHP 7.4 or greater.

## Local Dev Environment

To get started:

1. copy `docker/.env.template` to `docker/.env` and (optionally) update any values you like, then
2. run `init.sh` to do some housekeeping. Finally,
3. execute `docker compose -f docker/docker-compose.yml up`.

## Dev-Utils Setup

The example commands in this documentation expect `dev-utils` to be initialized by downloading
certain `.phar` files and loading .

Run the following command to download them:

```bash
./dev-utils/fetch-phars.sh
```

## Tests

To run tests locally:

```bash
./jds-demo-plugin/vendor/bin/codecept -c jds-demo-plugin run
```

or within docker (preferred):

```bash
# note: call with the "down" command to reset images (e.g. fresh MySQL state)
docker-compose -f docker/docker-compose.yml -f docker/docker-compose.tests.yml up --abort-on-container-exit --exit-code-from php
```

## Updating the .pot file

```bash
# extract info from the twig templates
php jds-demo-plugin/tasks/extract-twig-text.php

# run wp-cli to create/update the .pot file
./refresh-pot.sh
```

## Static Analysis

```bash
# PHPStan
./dev-utils/phpstan/vendor/bin/phpstan analyse --memory-limit 1G

# Psalm
./jds-demo-plugin/vendor/bin/psalm -c jds-demo-plugin/psalm.xml
```

## Linting

The call to `init.sh` should run composer and install PHP CS Fixer in `./dev-utils/php-cs-fixer`.

To fix files:

```bash
./dev-utils/php-cs-fixer/vendor/bin/php-cs-fixer fix
```

Integrating PHP CS Fixer with your IDE of choice is an exercise left to the reader.

## Production Build

Builds are scoped using [PHP Scoper](https://github.com/humbug/php-scoper) to ensure no dependency conflicts with other
WordPress plugins.

The call to `init.sh` should run composer and install PHP Scoper in '/dev-utils/scoper'.

To build:

```bash
./build.sh
```

To test drive the build on localhost:

```bash
./test-drive-release.sh
```

## Clean Start

To blow away docker resources related to this project:

```bash
# specify the relevant .yml file and call the "down" command
docker compose -f docker/docker-compose.yml down

# to completely reset:

docker compose -f docker/docker-compose.yml down --rmi all --volumes
```
