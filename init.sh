#!/bin/bash

cd "$(dirname "${BASH_SOURCE[0]}")" || ( echo "failed to enter the script directory" && exit 1 )

SCRIPT_DIR=$(pwd)

printf "executing within %s \n\n" "$SCRIPT_DIR"

# install composer dependencies

if [ ! -d "./jds-demo-plugin/vendor" ]; then
	php ./dev-utils/composer.phar --working-dir=jds-demo-plugin install
	echo "jds-demo-plugin dependencies installed"
fi

if [ ! -d "./dev-utils/php-cs-fixer/vendor" ]; then
	php ./dev-utils/composer.phar --working-dir=dev-utils/php-cs-fixer install
	echo "php-cs-fixer dependencies installed"
fi

if [ ! -d "./dev-utils/phpstan/vendor" ]; then
	php ./dev-utils/composer.phar --working-dir=dev-utils/phpstan install
	echo "phpstan dependencies installed"
fi

if [ ! -d "./dev-utils/scoper/vendor" ]; then
	php ./dev-utils/composer.phar --working-dir=dev-utils/scoper install
	echo "scoper dependencies installed"
fi

echo "all composer dependencies for development are installed"

# downloads wordpress and stages the plugin files

if [ -d "./wordpress/" ]; then
	echo "-- skipping wordpress download (already present)"
else
	curl https://wordpress.org/latest.tar.gz -o wordpress.tar.gz
	tar -xvf wordpress.tar.gz
fi

if [ -d "./wordpress/wp-content/plugins/jds-demo-plugin" ]; then
	rm -rf ./wordpress/wp-content/plugins/jds-demo-plugin
	echo "-- removed existing plugin files"
fi

cp -r jds-demo-plugin/ wordpress/wp-content/plugins/

echo "copied plugin files to wordpress installation"
echo ""

# backticks below are not intended to execute
# shellcheck disable=SC2016
echo 'execute `docker compose up` to launch, then visit localhost in your browser'
echo '(check .env to see if you need to specify a port)'
