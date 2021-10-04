#!/bin/bash
echo "initializing test user and database"
mysql --defaults-extra-file=/docker-entrypoint-initdb.d/root@localhost.cnf -uroot -e "CREATE DATABASE $MYSQL_TEST_DATABASE DEFAULT CHARACTER SET utf8;"
mysql --defaults-extra-file=/docker-entrypoint-initdb.d/root@localhost.cnf -uroot -e "CREATE USER '$MYSQL_TEST_USER'@'%' IDENTIFIED BY '$MYSQL_TEST_USER_PASSWORD';"
mysql --defaults-extra-file=/docker-entrypoint-initdb.d/root@localhost.cnf -uroot -e "GRANT ALL PRIVILEGES ON $MYSQL_TEST_DATABASE.* TO '$MYSQL_TEST_USER'@'%';"
mysql --defaults-extra-file=/docker-entrypoint-initdb.d/root@localhost.cnf -uroot -e "FLUSH PRIVILEGES;"
echo "done initializing test user and database"
