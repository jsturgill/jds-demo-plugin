echo "running mysql commands"
export MYSQL_PWD="$MYSQL_ROOT_PASSWORD"
mysql -uroot -e "CREATE DATABASE $MYSQL_TEST_DATABASE DEFAULT CHARACTER SET utf8;"
mysql -uroot -e "CREATE USER '$MYSQL_TEST_USER'@'%' IDENTIFIED BY '$MYSQL_TEST_USER_PASSWORD';"
mysql -uroot -e "GRANT ALL PRIVILEGES ON $MYSQL_TEST_DATABASE.* TO '$MYSQL_TEST_USER'@'%';"
mysql -uroot -e "FLUSH PRIVILEGES;"
