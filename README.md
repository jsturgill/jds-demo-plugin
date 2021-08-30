# Demo Plugin

Below are instructions on how to get the dev environment up and running.

The commands were tested under Windows git bash. It should be straightforward for you
to adjust as necessary for your environment.

Note: Docker compose is required.

## Local Dev Environment

To get started:

1. copy `.env.template` to `.env` and update any values, then
2. run `init.sh` to download WordPress, run docker install, and stage some files. Finally,
3. execute `docker compose up`.

## Dev-Utils Setup

The example commands in this documentation expect certain `phar` files to exist in `dev-utils`.

Run the following command to download them:

```bash
./dev-utils/fetch-phars.sh
```

## Tests

To run tests:

```bash
./jds-demo-plugin/vendor/bin/codecept -c jds-demo-plugin run
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
# specify the relevant .yml file
docker compose -f docker-compose.dev.yml down --rmi all --volumes
```