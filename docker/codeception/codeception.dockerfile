FROM codeception/codeception:4.1.21

COPY ./scripts /opt/scripts

RUN apt-get update \
    && apt-get install -y netcat \
    && chmod a+rx /opt/scripts/*.sh

ENTRYPOINT ["/opt/scripts/wait-for.sh", "chrome:4444/wd/hub", "--", "/opt/scripts/wait-for-wp-setup.sh"]
CMD ["codecept", "run"]



