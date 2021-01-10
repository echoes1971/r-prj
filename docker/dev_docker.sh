#!/bin/bash

PRJ_HOME=`cd ..; pwd`
RPRJ_IMG=rprj-mariadb-dev-image
PHP_APP=rprj-mariadb-dev-php
MYSQL_APP=rprj-mariadb
MYSQL_DB=rproject
MYSQL_PASSWORD=mysecret

if [ "$1" = "clean" ]; then
 echo "Stopping containers..."
 docker container stop $PHP_APP
 docker container stop $MYSQL_APP
 echo "Deleting image and containers...";
 docker container rm $PHP_APP
 docker container rm $MYSQL_APP
 docker image rm $RPRJ_IMG
 echo
#  docker container ls -a
#  docker image ls -a
#  exit 1
fi

# MySQL
MYSQL_EXISTS=`docker container ls -a | grep -v "$MYSQL_APP-image" | grep -v "$MYSQL_APP-php" | grep $MYSQL_APP`
#echo $MYSQL_EXISTS
if [ -n "$MYSQL_EXISTS" ]; then
 echo "* Container $MYSQL_APP exists"
 #docker container stop $MYSQL_APP
 #docker container rm $MYSQL_APP
 #echo "Access mysql with: docker exec -it rprj-mysql mysql -pmysecret"
 docker container start $MYSQL_APP
fi
if [ -z "$MYSQL_EXISTS" ]; then
 echo "* Creating container $MYSQL_APP"
 #docker container rm $MYSQL_APP
 docker run \
  -p 3306:3306 \
  --name $MYSQL_APP \
  -v $PRJ_HOME/mariadb:/var/lib/mysql \
  -v $PRJ_HOME/config/mysql:/etc/mysql/conf.d \
  -e MYSQL_ROOT_PASSWORD=$MYSQL_PASSWORD \
  -d mariadb:10.3
 #echo "Initialize DB with: docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e \"create database $MYSQL_DB;\""

 echo -n "Creating DB"
 docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e "create database if not exists $MYSQL_DB;" > /dev/null 2&>1
 retVal=$?
 while [ $retVal -ne 0 ]; do
  echo -n "."
  sleep 1
  docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e "create database if not exists $MYSQL_DB;" > /dev/null
  retVal=$?
 done
 echo " done."
fi

# #### Create PHP Image
# Config_local
cp ../php/config_local.sample.php ../php/config_local.php
sed -i s/rprj-db-server/$MYSQL_APP/g ../php/config_local.php
sed -i s/rprj-db-db/$MYSQL_DB/g ../php/config_local.php
sed -i s/rprj-db-pwd/$MYSQL_PASSWORD/g ../php/config_local.php

IMG_EXISTS=`docker image ls | grep $RPRJ_IMG`
#echo $IMG_EXISTS
# See: http://timmurphy.org/2010/05/19/checking-for-empty-string-in-bash/
if [ -n "$IMG_EXISTS" ]; then
 echo "* Image $RPRJ_IMG exists"
fi
if [ -z "$IMG_EXISTS" ]; then
 echo "* Creating image $RPRJ_IMG"
 docker build -f Dockerfile_dev -t $RPRJ_IMG .
fi


# #### PHP
PHP_EXISTS=`docker container ls -a | grep $PHP_APP`
#echo $PHP_EXISTS
if [ -n "$PHP_EXISTS" ]; then
 echo "* Container $PHP_APP exists"
 #docker container stop $PHP_APP
 #docker container rm $PHP_APP
 docker container start $PHP_APP
fi
if [ -z "$PHP_EXISTS" ]; then
 echo "* Creating container $PHP_APP"
 #docker container rm $PHP_APP
 docker run -p 8080:80 --name $PHP_APP \
 -v "$PRJ_HOME/php":/var/www/html \
 -v "$PRJ_HOME/files":/var/www/html/files \
 -v "$PRJ_HOME/files":/var/www/html/mng/files \
 --link $MYSQL_APP:mysql \
 -d $RPRJ_IMG

 # Init DB
 docker exec -it $PHP_APP bash -c "cd /var/www/html/mng/ ; php db_update_do.php" > /dev/null
 retVal=$?
 while [ $retVal -ne 0 ]; do
  echo -n "."
  sleep 1
  docker exec -it $PHP_APP bash -c "cd /var/www/html/mng/ ; php db_update_do.php" > /dev/null
  retVal=$?
 done
 docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e "show tables;" $MYSQL_DB
fi


echo "Access mysql with: docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD"
echo "Interact with the containers with:"
echo " docker exec -it $MYSQL_APP bash"
echo " docker exec -it $PHP_APP bash"
echo "Point your browser to: http://localhost:8080/"
echo "Initialize DB with: http://localhost:8080/mng/db_update.php"
read -p "Press any key to continue... " -n1 -s
echo

docker container stop $PHP_APP
docker container stop $MYSQL_APP

echo "docker container rm $PHP_APP"
echo "docker container rm $MYSQL_APP"
