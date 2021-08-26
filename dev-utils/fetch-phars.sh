#!/bin/bash

cd "$(dirname "${BASH_SOURCE[0]}")" || ( echo "failed to enter the script directory" && exit 1 )

SCRIPT_DIR=$(pwd)

printf "executing within %s \n\n" "$SCRIPT_DIR"

curl https://getcomposer.org/download/latest-stable/composer.phar -O
curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -O
