FROM nginx:stable-alpine

ADD ./nginx/default.conf /etc/nginx/conf.d/default.conf

COPY ./files/wordpress /var/www/html/wordpress
COPY ./scripts/call-bash-script.php /var/www/html
