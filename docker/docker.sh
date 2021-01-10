#!/bin/bash

PRJ_HOME=`cd ..; pwd`
RPRJ_IMG_BASE=rprj-php-base
RPRJ_IMG=rprj-mariadb-image
PHP_APP=rprj-php-mariadb
MYSQL_APP=rprj-mariadb
MYSQL_DB=rproject
MYSQL_PASSWORD=mysecret

if [ "$1" = "clean" ] || [ "$1" = "cleanall" ]; then
 echo "Stopping containers..."
 docker container stop $PHP_APP
 docker container stop $MYSQL_APP
 echo "Deleting image and containers...";
 docker container rm $PHP_APP
 docker container rm $MYSQL_APP
 docker image rm $RPRJ_IMG
 if [ "$1" = "cleanall" ]; then
  docker image rm $RPRJ_IMG_BASE
 fi
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
 docker container start $MYSQL_APP
fi
if [ -z "$MYSQL_EXISTS" ]; then
 echo "* Creating container $MYSQL_APP"
 docker run \
  -p 3306:3306 \
  --name $MYSQL_APP \
  -v $PRJ_HOME/mariadb:/var/lib/mysql \
  -v $PRJ_HOME/config/mysql:/etc/mysql/conf.d \
  -e MYSQL_ROOT_PASSWORD=$MYSQL_PASSWORD \
  -d mariadb:10.3
 #echo "Initialize DB with: docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e \"create database $MYSQL_DB;\""

 echo -n "Waiting DB..."
 docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e "show databases;" > /dev/null
 retVal=$?
 while [ $retVal -ne 0 ]; do
  echo -n "."
  sleep 1
  docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e "show databases;" > /dev/null
  retVal=$?
 done
 echo " online."

# ## Moved inside db_update_do.php
#  echo -n "Creating DB..."
#  docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e "create database if not exists $MYSQL_DB;" > /dev/null 2&>1
#  retVal=$?
#  while [ $retVal -ne 0 ]; do
#   echo -n "."
#   sleep 1
#   docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e "create database if not exists $MYSQL_DB;" > /dev/null
#   retVal=$?
#  done
#  echo " done."
fi

# #### Create PHP Image
# Copy sources
rm -rf build
mkdir build
cp -R ../php/* ./build/

# # Config_local
# cp ../php/config_local.sample.php ./build/config_local.php
# sed -i s/rprj-db-server/$MYSQL_APP/g ./build/config_local.php
# sed -i s/rprj-db-db/$MYSQL_DB/g ./build/config_local.php
# sed -i s/rprj-db-pwd/$MYSQL_PASSWORD/g ./build/config_local.php
# #sed -i s/setVerbose\(false\)/setVerbose\(true\)/g ./build/mng/db_update_do.php

IMG_BASE_EXISTS=`docker image ls | grep $RPRJ_IMG_BASE`
#echo $IMG_BASE_EXISTS
# See: http://timmurphy.org/2010/05/19/checking-for-empty-string-in-bash/
if [ -n "$IMG_BASE_EXISTS" ]; then
 echo "* Image $RPRJ_IMG_BASE exists"
fi
if [ -z "$IMG_BASE_EXISTS" ]; then
 echo "* Creating image $RPRJ_IMG_BASE"
 docker build -f ./Dockerfile_base -t $RPRJ_IMG_BASE .
fi

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


# #### PHP
PHP_EXISTS=`docker container ls -a | grep $PHP_APP`
#echo $PHP_EXISTS
if [ -n "$PHP_EXISTS" ]; then
 echo "* Container $PHP_APP exists"
 docker container start $PHP_APP
fi
if [ -z "$PHP_EXISTS" ]; then
 echo "* Creating container $PHP_APP"
 docker run -p 8080:80 --name $PHP_APP \
 -e MYSQL_APP=$MYSQL_APP \
 -e MYSQL_DB=$MYSQL_DB \
 -e MYSQL_PASSWORD=$MYSQL_PASSWORD \
 -v "$PRJ_HOME/files":/var/www/html/files \
 -v "$PRJ_HOME/files":/var/www/html/mng/files \
 --link $MYSQL_APP:mysql \
 -d $RPRJ_IMG

 # Init DB
 echo -n "Init DB..."
 docker exec -it $PHP_APP bash -c "cd /var/www/html/mng/ ; php db_update_do.php" > /dev/null
 retVal=$?
 while [ $retVal -ne 0 ]; do
  echo -n "."
  sleep 1
  docker exec -it $PHP_APP bash -c "cd /var/www/html/mng/ ; php db_update_do.php" > /dev/null
  retVal=$?
 done
 echo " done!"
 #docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e "show tables;" $MYSQL_DB
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
