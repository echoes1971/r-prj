#!/bin/bash

PRJ_HOME=`cd ..; pwd`
# RPRJ_IMG_DB=rprj-img-db-mariadb
# RPRJ_IMG_BASE=rprj-img-php
# RPRJ_IMG=rprj-img-app
# PHP_APP=rprj-php-mariadb
# MYSQL_APP=rprj-mariadb
# MYSQL_DB=rproject
# MYSQL_PASSWORD=mysecret

. ./docker.config

if [ "$1" = "clean" ] || [ "$1" = "cleanall" ]; then
 echo "Stopping containers..."
 docker container stop $PHP_APP
 docker container stop $MYSQL_APP
 echo "Deleting image and containers...";
 docker container rm $PHP_APP
 docker container rm $MYSQL_APP
 docker image rm $RPRJ_IMG
 docker image rm $RPRJ_IMG_DB
 if [ "$1" = "cleanall" ]; then
  docker image rm $RPRJ_IMG_BASE
 fi
 echo
#  docker container ls -a
#  docker image ls -a
 exit 1
fi


# #### Creating Images ####

# MySQL
IMG_DB_EXISTS=`docker image ls | grep $RPRJ_IMG_DB`
#echo $IMG_DB_EXISTS
# See: http://timmurphy.org/2010/05/19/checking-for-empty-string-in-bash/
if [ -n "$IMG_DB_EXISTS" ]; then
 echo "* Image $RPRJ_IMG_DB exists"
fi
if [ -z "$IMG_DB_EXISTS" ]; then
 cp $PRJ_HOME/config/mysql/disable_strict_mode.cnf .
 echo "* Creating image $RPRJ_IMG_DB"
 docker build -f ./Dockerfile_db -t $RPRJ_IMG_DB .
 rm disable_strict_mode.cnf
fi

# PHP Base
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

# R-Prj Image
IMG_EXISTS=`docker image ls | grep $RPRJ_IMG`
#echo $IMG_EXISTS
# See: http://timmurphy.org/2010/05/19/checking-for-empty-string-in-bash/
if [ -n "$IMG_EXISTS" ]; then
 echo "* Image $RPRJ_IMG exists"
fi
if [ -z "$IMG_EXISTS" ]; then
 # Copy sources
 rm -rf build
 mkdir build
 cp -R $PRJ_HOME/php/* ./build/
 echo "* Creating image $RPRJ_IMG"
 docker build -t $RPRJ_IMG .
fi


if [ "$1" = "images" ]; then
 # build only the images and then exits
 docker image ls
 exit 0
fi


# #### Creating Containers ####

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
  -e MYSQL_ROOT_PASSWORD=$MYSQL_PASSWORD \
  -d $RPRJ_IMG_DB
#   -v $PRJ_HOME/config/mysql:/etc/mysql/conf.d \
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
