#!/bin/bash

PRJ_HOME=`cd ..; pwd`
RPRJ_IMG=rprj-mariadb-image
PHP_APP=rprj-php-mariadb
MYSQL_APP=rprj-mariadb
MYSQL_DB=rproject
MYSQL_PASSWORD=mysecret

if [ "$1" = "clean" ]; then
 echo "Deleting image and containers...";
 docker container rm $PHP_APP
 docker container rm $MYSQL_APP
 docker image rm $RPRJ_IMG
 echo
#  docker container ls -a
#  docker image ls -a
 exit 1
fi

# MySQL
MYSQL_EXISTS=`docker container ls -a | grep $MYSQL_APP | grep -v $MYSQL_APP-dev-php`
#echo $MYSQL_EXISTS
if [ -n "$MYSQL_EXISTS" ]; then
 echo "* Container $MYSQL_APP exists"
 #docker container stop $MYSQL_APP
 #docker container rm $MYSQL_APP
 #echo "Access mysql with: docker exec -it rprj-mysql mysql -p$MYSQL_PASSWORD"
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
 echo "Initialize DB with: docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e \"create database $MYSQL_DB;\""
 docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e "create database $MYSQL_DB if not exists;"
fi

# #### Create PHP Image
# Copy sources
rm -rf build
mkdir build
cp -R ../php/* ./build/
sed -i s/rprj-mysql/$MYSQL_APP/g ./build/config_local.php
sed -i s/rproject/$MYSQL_DB/g ./build/config_local.php
sed -i s/mysecret/$MYSQL_PASSWORD/g ./build/config_local.php
sed -i s/setVerbose\(false\)/setVerbose\(true\)/g ./build/mng/db_update_do.php

IMG_EXISTS=`docker image ls | grep $RPRJ_IMG`
#echo $IMG_EXISTS
# See: http://timmurphy.org/2010/05/19/checking-for-empty-string-in-bash/
if [ -n "$IMG_EXISTS" ]; then
 echo "* Image $RPRJ_IMG exists"
fi
if [ -z "$IMG_EXISTS" ]; then
 echo "* Creating image $RPRJ_IMG"
 docker build -t $RPRJ_IMG .
fi

# #### MYSQL: create DB if not exists. At this point a new /var/lib/mmysql folder should have been created
# if [ -z "$MYSQL_EXISTS" ]; then
 docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e "create database if not exists $MYSQL_DB;"
# fi

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
 -v "$PRJ_HOME/files":/var/www/html/files \
 -v "$PRJ_HOME/files":/var/www/html/mng/files \
 --link $MYSQL_APP:mysql \
 -d $RPRJ_IMG
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
