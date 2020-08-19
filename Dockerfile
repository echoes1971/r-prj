FROM php:7.4-apache

ENV SITE_TITLE My Site
ENV SITE_TITLE_2 My cool subtitle
ENV MYSQL_APP rprj-db
ENV MYSQL_DB rproject
ENV MYSQL_PASSWORD mysecret
ENV RPRJ_ADMIN_PASS myrprjsecret
ENV RPRJ_SKIN default
ENV RPRJ_ROOT_OBJ -10

RUN apt-get update -y
RUN apt-get install -y apt-utils zlib1g-dev libzip-dev

# See: https://stackoverflow.com/questions/39657058/installing-gd-in-docker
RUN apt-get install -y libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev libfreetype6-dev

# Already loaded?
# RUN docker-php-ext-install mbstring

# Install mysqli
RUN docker-php-ext-install mysqli
# RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

RUN docker-php-ext-install zip

RUN docker-php-ext-configure gd --enable-gd --with-webp --with-jpeg --with-xpm --with-freetype
RUN docker-php-ext-install gd

# Copy the source
COPY php/ /var/www/html/

COPY docker/webentrypoint.sh /usr/local/bin/
COPY docker/webentrypoint.sh /usr/local/bin/docker-php-entrypoint

# ENTRYPOINT /usr/local/bin/webentrypoint.sh
