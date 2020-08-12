FROM php:7.4-apache

ENV SITE_TITLE My Site
ENV MYSQL_APP rprj-db
ENV MYSQL_DB rproject
ENV MYSQL_PASSWORD mysecret

RUN apt-get update -y
RUN apt-get install -y zlib1g-dev libzip-dev

# Install mysqli
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# See: https://stackoverflow.com/questions/39657058/installing-gd-in-docker
RUN apt-get install -y apt-utils \
    libwebp-dev libjpeg62-turbo-dev libpng-dev libxpm-dev \
    libfreetype6-dev

# Already loaded?
# RUN docker-php-ext-install mbstring

RUN docker-php-ext-install zip

RUN docker-php-ext-configure gd
# RUN docker-php-ext-configure gd --with-gd --with-webp-dir --with-jpeg-dir \
#     --with-png-dir --with-zlib-dir --with-xpm-dir --with-freetype-dir \
#     --enable-gd-native-ttf

RUN docker-php-ext-install gd

# Copy the source
COPY php/ /var/www/html/

COPY webentrypoint.sh /usr/local/bin/
COPY docker/webentrypoint.sh /usr/local/bin/docker-php-entrypoint

# ENTRYPOINT /usr/local/bin/webentrypoint.sh
