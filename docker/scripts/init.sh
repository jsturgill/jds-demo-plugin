#!/bin/sh

/var/scripts/wait-for.sh "$DBHOST:3306"

if [ $? -eq 0 ]; then
    echo "mysql is available"
else
    echo "Could not reach mysql"
    exit 1
fi

echo "creating config"
wp config create --dbname="$DBNAME" --dbuser="$DBUSER" --dbpass="$DBPASS" --dbhost="$DBHOST" --path=/var/www/html --allow-root

# not needed -- database is created according to .env file already
# echo "creating database"
# wp db create --path=/var/www/html --allow-root

echo "installing"
wp core install --url="$URL" --title="$TITLE" --admin_user="$ADMIN_USER" --admin_password="$ADMIN_PASSWORD" --admin_email="$ADMIN_EMAIL" --path=/var/www/html --allow-root

echo "activating plugin"
wp plugin activate jds-demo-plugin --path=/var/www/html --allow-root

#cd /var/www/html || exit
echo "starting PHP FPM"
php-fpm
sleep infinite
