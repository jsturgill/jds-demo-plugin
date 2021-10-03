FROM codeception/codeception:4.1.21

# wordpress stuff
ARG wp_admin_password
ARG wp_admin_user
ARG wp_host
ARG wp_path

# db stuff
ARG dbname
ARG dbuser
ARG dbpass
ARG dbhost

# wordpress stuff
ENV WP_ADMIN_USER=$wp_admin_user
ENV WP_ADMIN_PASSWORD=$wp_admin_password
ENV WP_HOST=$wp_host
ENV WP_PATH=$wp_path

# database stuff
ENV DBNAME=$dbname
ENV DBUSER=$dbuser
ENV DBPASS=$dbpass
ENV DBHOST=$dbhost

COPY ./scripts /opt/scripts

RUN apt-get update \
    && apt-get install -y netcat \
    && chmod a+rx /opt/scripts/*.sh

ENTRYPOINT ["/opt/scripts/wait-for.sh", "chrome:4444/wd/hub", "--", "/opt/scripts/wait-for-wp-setup.sh"]
CMD ["codecept", "run"]



