#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
  set -- apache2-foreground "$@"
fi

# echo "  ___     ___     _ "
# echo " | _ \___| _ \_ _(_)"
# echo " |   /___|  _/ '_| |"
# echo " |_|_\   |_| |_|_/ |"
# echo "               |__/ "
echo "  ___     ___          _        _   "
echo " | _ \___| _ \_ _ ___ (_)___ __| |_ "
echo " |   /___|  _/ '_/ _ \| / -_) _|  _|"
echo " |_|_\   |_| |_| \___// \___\__|\__|"
echo "                    |__/            "
echo
echo "(C) 2005-2020 by Roberto Rocco-Angeloni"
echo
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
sed -i s/adm\',\'adm\'/adm\',\'$RPRJ_ADMIN_PASS\'/g /var/www/html/mng/db_update_do.php

cd /var/www/html/mng/ ; php /var/www/html/mng/docker_waitdb.php # > /dev/null 2>&1
cd /var/www/html/mng/ ; php /var/www/html/mng/db_update_do.php > /var/log/webentrypoint.log 2>&1

chmod 777 /var/www/html/files
chmod 777 /var/www/html/mng/files

exec "$@" 
