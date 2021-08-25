#!/bin/bash

if [ -d "./wordpress/wp-content/plugins/jds-demo-plugin" ]; then
	rm -rf ./wordpress/wp-content/plugins/jds-demo-plugin
	echo "-- removed existing plugin files"
fi

if ! [ -d "./build" ]; then
	echo "./build directory not found"
	exit 1
fi

mkdir -p wordpress/wp-content/plugins/jds-demo-plugin/

cp -r build/* wordpress/wp-content/plugins/jds-demo-plugin/

echo "copied release plugin files to wordpress installation"
echo ""

# backticks below are not intended to execute
# shellcheck disable=SC2016
echo 'execute `docker compose up` to launch, then visit localhost in your browser'
echo '(check .env to see if you need to specify a port)'
