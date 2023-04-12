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
echo "(C) 2005-2022 by Roberto Rocco-Angeloni"
echo
echo "Environment:"
echo "$SERVER_NAME"
echo "$SITE_TITLE"
echo "$SITE_TITLE_2"
echo "$MYSQL_APP"
echo "$MYSQL_DB"
echo "$MYSQL_PASSWORD"
echo "$RPRJ_SKIN"
echo "$RPRJ_ROOT_OBJ"
echo "$RPRJ_ADMIN_PASS"
echo "$RPRJ_DB_SCHEMA"
echo "==============="

# Write configuration
cp /var/www/html/config_local.sample.php /var/www/html/config_local.php
sed -i "s/__site_title__/$SITE_TITLE/g" /var/www/html/config_local.php
sed -i "s/__site_title_2__/$SITE_TITLE_2/g" /var/www/html/config_local.php
sed -i s/rprj-db-server/$MYSQL_APP/g /var/www/html/config_local.php
sed -i s/rprj-db-db/$MYSQL_DB/g /var/www/html/config_local.php
sed -i s/rprj-db-pwd/$MYSQL_PASSWORD/g /var/www/html/config_local.php
sed -i s/rprj-db-schema/$RPRJ_DB_SCHEMA/g /var/www/html/config_local.php
sed -i "s/skin = 'default'/skin = '$RPRJ_SKIN'/g" /var/www/html/config_local.php
sed -i s/-10/$RPRJ_ROOT_OBJ/g /var/www/html/config_local.php
# sed -i s/setVerbose\(false\)/setVerbose\(true\)/g /var/www/html/mng/db_update_do.php

sed -i s/_servername_com_/$SERVER_NAME/g /etc/apache2/sites-available/000-default.conf


# Wait DB to be online and then run the db_update_do.php
sed -i s/adm\',\'adm\'/adm\',\'$RPRJ_ADMIN_PASS\'/g /var/www/html/mng/db_update_do.php
cd /var/www/html/mng/ ; php /var/www/html/mng/docker_waitdb.php # > /dev/null 2>&1
cd /var/www/html/mng/ ; php /var/www/html/mng/db_update_do.php > /var/log/webentrypoint.log 2>&1
sed -i s/adm\',\'$RPRJ_ADMIN_PASS\'/adm\',\'adm\'/g /var/www/html/mng/db_update_do.php

chmod 777 /var/www/html/files
chmod 777 /var/www/html/mng/files

exec "$@" 
