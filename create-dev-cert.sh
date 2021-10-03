#!/bin/bash

IFS='
'
. docker/.env

openssl req -nodes -newkey rsa:4096 -keyout docker/nginx/files/dev.key -out docker/nginx/files/dev.csr -subj \
  "/C=US/ST=California/L=Fresno/O=JS Designs/OU=Development/CN=*.$ALIAS_NGINX"
openssl x509 -in docker/nginx/files/dev.csr -out docker/nginx/files/dev.cert -req -signkey docker/nginx/files/dev.key -days 365
