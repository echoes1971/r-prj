#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
  set -- apache2-foreground "$@"
fi

echo "  ___     ___     _ "
echo " | _ \___| _ \_ _(_)"
echo " |   /___|  _/ '_| |"
echo " |_|_\   |_| |_|_/ |"
echo "               |__/ "

# echo "Environment:"
# echo "$SITE_TITLE"
# echo "$MYSQL_APP"
# echo "$MYSQL_DB"
# echo "$MYSQL_PASSWORD"
# echo "==============="

sed -i "s/:: R-Project ::/$SITE_TITLE/g" /var/www/html/config_local.php
sed -i s/rprj-mariadb/$MYSQL_APP/g /var/www/html/config_local.php
sed -i s/rproject/$MYSQL_DB/g /var/www/html/config_local.php
sed -i s/mysecret/$MYSQL_PASSWORD/g /var/www/html/config_local.php
# sed -i s/setVerbose\(false\)/setVerbose\(true\)/g /var/www/html/mng/db_update_do.php

# cd /var/www/html/mng/ ; php /var/www/html/mng/db_update_do.php # > /dev/null 2>&1

exec "$@"
