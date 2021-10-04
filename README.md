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
certain `.phar` files and installing composer dependencies. All of this is taken care of by 
running `init.sh` in the root directory.

## Dev SSL Cert

A development SSL cert and key are placed in `docker/nginx/files` when `init.sh` is run.

If relevant aliases are changed in `docker/.env`, the cert may need to be rebuilt. You can do this by running
`./create-dev-cert.sh` at any time.

## Tests

To run tests within docker:

```bash
# dev tests
docker-compose -f docker/docker-compose.yml -f docker/docker-compose.tests.yml up --exit-code-from codeception --abort-on-container-exit
# prod build test
./build.sh && docker-compose -f docker/docker-compose.yml -f docker/docker-compose.tests.yml -f docker/docker-compose.tests.prod.yml up --exit-code-from codeception --abort-on-container-exit
```

To create a new test:

```bash
./jds-demo-plugin/vendor/bin/codecept -c jds-demo-plugin generate:test unit FileSystem
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
docker-compose -f docker/docker-compose.yml -f docker/docker-compose.serve-prod-build.yml up --abort-on-container-exit --exit-code-from php
```

Then visit `https://{ALIAS_NGINX}/wordpress`. You will likely be prompted to accept a self-signed certificate.

Note: `{ALIAS_NGINX}` is defined in `docker/.env` and defaults to `jdsdemo.test`

Note: this requires an entry in your hosts file pointing host `{ALIAS_NGINX}` to localhost.  In windows, the hosts file is 
located at `C:\Windows\System32\drivers\etc\hosts`. In linux, try `/etc/hosts`.

An example hosts entry:

```bash
# values from docker/.env aliases
127.0.0.1 jdsdemo.test mysql.jdsdemo.test chrome.jdsdemo.test
```

## Clean Start

To blow away docker resources related to this project:

```bash
# specify the relevant .yml file(s) and call the "down" command
docker compose -f docker/docker-compose.yml down

# to completely reset, include `--rmi all --volumes`:
docker compose -f docker/docker-compose.yml down --rmi all --volumes

# to remove all images
docker rmi $(docker images -a -q)
```
