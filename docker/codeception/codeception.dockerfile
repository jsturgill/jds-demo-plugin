FROM codeception/codeception:4.1.21

# wordpress stuff
ARG admin_password
ARG admin_user

# db stuff
ARG dbname
ARG dbuser
ARG dbpass
ARG dbhost

# wordpress stuff
ENV ADMIN_USER=$admin_user
ENV ADMIN_PASSWORD=$admin_password

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



