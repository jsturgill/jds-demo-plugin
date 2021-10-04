FROM mysql:8

COPY ./scripts/mysql/* /docker-entrypoint-initdb.d/
RUN touch /docker-entrypoint-initdb.d/root@localhost.cnf \
    && chown mysql:mysql /docker-entrypoint-initdb.d/root@localhost.cnf
