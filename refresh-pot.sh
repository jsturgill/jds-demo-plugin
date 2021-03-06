#!/bin/bash

cd "$(dirname "${BASH_SOURCE[0]}")" || ( echo "failed to enter the script directory" && exit 1 )

SCRIPT_DIR=$(pwd)

printf "executing within %s \n\n" "$SCRIPT_DIR"

php ./dev-utils/wp-cli.phar i18n make-pot ./jds-demo-plugin/cache/gettext jds-demo-plugin/cache/gettext/templates.pot --ignore-domain
php ./dev-utils/wp-cli.phar i18n make-pot ./jds-demo-plugin/src jds-demo-plugin/languages/jds-demo-plugin.pot --merge=jds-demo-plugin/cache/gettext/templates.pot --ignore-domain

printf "\njds-demo-plugin/languages/jds-demo-plugin.pot updated"
