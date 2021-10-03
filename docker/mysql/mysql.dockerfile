FROM mysql:8

COPY ./scripts/mysql/* /docker-entrypoint-initdb.d/
