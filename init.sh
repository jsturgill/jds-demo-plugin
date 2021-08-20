#!/bin/bash

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
echo 'execute `docker compose up` to launch'
