#!/bin/bash
echo "
[client]
password=\"$MYSQL_ROOT_PASSWORD\"
" > /docker-entrypoint-initdb.d/root@localhost.cnf
