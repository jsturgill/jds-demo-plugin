#!/bin/sh

/opt/scripts/wait-for.sh "$DBHOST:3306"

php /var/www/html/wordpress/wp-content/plugins/jds-demo-plugin/tasks/clean-cache.php
php /var/www/html/wordpress/wp-content/plugins/jds-demo-plugin/tasks/clean-logs.php

if [ $? -eq 0 ]; then
    echo "mysql is available"
else
    echo "Could not reach mysql"
    exit 1
fi

echo "creating WP config for mysql host $DBHOST"
wp config create --dbname="$DBNAME" --dbuser="$DBUSER" --dbpass="$DBPASS" --dbhost="$DBHOST" --path=/var/www/html/wordpress --allow-root

# not needed -- database is created according to .env file already
# echo "creating database"
# wp db create --path=/var/www/html --allow-root

echo "installing WP with URL $URL"
wp core install --url="$URL" --title="$TITLE" --admin_user="$ADMIN_USER" --admin_password="$ADMIN_PASSWORD" --admin_email="$ADMIN_EMAIL" --path=/var/www/html/wordpress --allow-root

echo "activating plugin"
wp plugin activate jds-demo-plugin --path=/var/www/html/wordpress --allow-root
