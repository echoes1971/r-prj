FROM rprj-php-base

ENV SERVER_NAME www.r-prj.ch
ENV SITE_TITLE :: R-Project ::
ENV SITE_TITLE_2 :: Project and Time Tracking Management System ::
ENV MYSQL_APP rprj-db
ENV MYSQL_DB rproject
ENV MYSQL_PASSWORD mysecret
ENV RPRJ_SKIN default
ENV RPRJ_ROOT_OBJ -10
ENV RPRJ_ADMIN_PASS adm

# Copy the source
COPY build/ /var/www/html/

# COPY webentrypoint.sh /usr/local/bin/
COPY webentrypoint.sh /usr/local/bin/docker-php-entrypoint
# ENTRYPOINT /usr/local/bin/webentrypoint.sh

# I know, it's totally stupid to have an apache serving a single host
COPY rprj.conf /etc/apache2/sites-available/000-default.conf

