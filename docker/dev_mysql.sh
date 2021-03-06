#!/bin/bash

PRJ_HOME=`cd ..; pwd`
RPRJ_IMG=rprj-dev-image
PHP_APP=rprj-dev-php
MYSQL_APP=rprj-mysql

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

# MySQL
MYSQL_EXISTS=`docker container ls -a | grep $MYSQL_APP`
#echo $MYSQL_EXISTS
if [ -n "$MYSQL_EXISTS" ]; then
 echo "* Container $MYSQL_APP exists"
 #docker container stop $MYSQL_APP
 #docker container rm $MYSQL_APP
 #echo "Access mysql with: docker exec -it rprj-mysql mysql -pmysecret"
 sed -i s/rprj-mariadb/$MYSQL_APP/g ../php/config_local.php
 docker container start $MYSQL_APP
fi
if [ -z "$MYSQL_EXISTS" ]; then
 echo "* Creating container $MYSQL_APP"
 #docker container rm $MYSQL_APP
 docker run \
  -p 3306:3306 \
  --name $MYSQL_APP \
  -v $PRJ_HOME/data:/var/lib/mysql \
  -v $PRJ_HOME/config/mysql:/etc/mysql/conf.d \
  -e MYSQL_ROOT_PASSWORD=mysecret \
  -d mysql:5.7
 echo "Initialize DB with: docker exec -it $MYSQL_APP mysql -pmysecret -e \"create database rproject;\""
fi

# PHP
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
 --link $MYSQL_APP:mysql \
 -d $RPRJ_IMG
fi

echo "Access mysql with: docker exec -it rprj-mysql mysql -pmysecret"
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
