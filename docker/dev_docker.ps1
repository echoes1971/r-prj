
cd ..
$PRJ_HOME = (Get-Location).Path
cd docker
$RPRJ_IMG="rprj-mariadb-dev-image"
$PHP_APP="rprj-mariadb-dev-php"
$MYSQL_APP="rprj-mariadb"
$MYSQL_DB="rproject"
$MYSQL_PASSWORD="mysecret"

Write-Output "args: $args"
Write-Output "PRJ_HOME: $PRJ_HOME"
Write-Output "PHP_APP: $PHP_APP"

if($args[0] -eq "clean") {
    Write-Output "Stopping containers..."
    docker container stop $PHP_APP
    docker container stop $MYSQL_APP
    Write-Output "Deleting image and containers...";
    docker container rm $PHP_APP
    docker container rm $MYSQL_APP
    docker image rm $RPRJ_IMG
    Write-Output ""
#  docker container ls -a
#  docker image ls -a
#  exit 1
}

# MySQL
$MYSQL_EXISTS = (docker container ls -a).replace("$MYSQL_APP-image","").replace("$MYSQL_APP-php","").indexof($MYSQL_APP) -gt -1
Write-Output $MYSQL_EXISTS
if($MYSQL_EXISTS) {
    Write-Output "* Container $MYSQL_APP exists"
    #docker container stop $MYSQL_APP
    #docker container rm $MYSQL_APP
    Write-Output "Access mysql with: docker exec -it $MYSQL_APP mysql -pmysecret"
    docker container start $MYSQL_APP
} else {
    Write-Output "* Creating container $MYSQL_APP"
    #docker container rm $MYSQL_APP
    docker run -p 3306:3306 --name $MYSQL_APP \
     -v $PRJ_HOME/mariadb:/var/lib/mysql \
     -v $PRJ_HOME/config/mysql:/etc/mysql/conf.d \
     -e MYSQL_ROOT_PASSWORD=$MYSQL_PASSWORD \
     -d mariadb:10.7
    # -d mariadb:10.3
    #Write-Output "Initialize DB with: docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e \"create database $MYSQL_DB;\""
   
    Write-Output -n "Creating DB"
    docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e "create database if not exists $MYSQL_DB;" > /dev/null 2&>1
    retVal=$?
    while [ $retVal -ne 0 ]; do
     Write-Output -n "."
     sleep 1
     docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD -e "create database if not exists $MYSQL_DB;" > /dev/null
     retVal=$?
    done
    Write-Output " done."
   }


# docker run \
# -p 3306:3306 \
# --name $MYSQL_APP \
# -v $PRJ_HOME/mariadb:/var/lib/mysql \
# -v $PRJ_HOME/config/mysql:/etc/mysql/conf.d \
# -e MYSQL_ROOT_PASSWORD=$MYSQL_PASSWORD \
# -d mariadb:10.7