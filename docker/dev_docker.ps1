
# Write-Output ""  | Out-File -Encoding "ASCII" .\dev_docker.log
Write-Output "" > .\dev_docker.log

$SITE_TITLE_1=":: R-Prj ::"
$SITE_TITLE_2="-= Development version =-"
Set-Location ..
$PRJ_HOME = (Get-Location).Path
Set-Location docker
$RPRJ_IMG="rprj-mariadb-dev-image"
$PHP_APP="rprj-mariadb-dev-php"
$MYSQL_APP="rprj-mariadb"
$MYSQL_DB="rproject"
$MYSQL_PASSWORD="mysecret"

# Write-Output "args: $args"

function ContainerExists {
    param( $_Name )

    (docker container ls -a | out-string).replace("${_Name}-image","").replace("{$_Name}-php","").indexof($_Name) -gt -1
}

function ImageExists {
    param( $_Name )

    (docker image ls -a | out-string).replace("${_Name}-image","").replace("{$_Name}-php","").indexof($_Name) -gt -1
}




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
$MYSQL_EXISTS = ContainerExists($MYSQL_APP)
if($MYSQL_EXISTS) {
    Write-Output "* Container $MYSQL_APP exists"
    docker container start $MYSQL_APP
} else {
    Write-Output "* Creating container $MYSQL_APP"
    docker run -p 3306:3306 --name $MYSQL_APP `
        -v $PRJ_HOME/mariadb:/var/lib/mysql `
        -v $PRJ_HOME/config/mysql:/etc/mysql/conf.d `
        -e MYSQL_ROOT_PASSWORD=$MYSQL_PASSWORD `
        -d mariadb:10.7
    # -d mariadb:10.3
    Write-Output "Initialize DB with: docker exec -it $MYSQL_APP mysql -p${MYSQL_PASSWORD} -e `"create database $MYSQL_DB;`""

    Write-Host -NoNewline "Creating DB"
    docker exec -it $MYSQL_APP mysql -uroot --password=${MYSQL_PASSWORD} -e "create database if not exists $MYSQL_DB;"
    #$lastSuccess = $?
    $retVal=$LASTEXITCODE
    #Write-Output "lastSuccess: $lastSuccess"
    #Write-Output "retVal: $retVal"
    while($retVal -ne 0) {
        Write-Host -NoNewline "."
        Start-Sleep -Seconds 2
        docker exec -it $MYSQL_APP mysql -uroot --password=${MYSQL_PASSWORD} -e "create database if not exists $MYSQL_DB;"
        #$lastSuccess = $?
        $retVal=$LASTEXITCODE
        #Write-Host -NoNewline "lastSuccess: $lastSuccess"
        #Write-Host -NoNewline "${retVal}."
    }
    Write-Output " done."
}

# #### Create PHP Image
# # Config_local
$config_local = (Get-Content ..\php\config_local.sample.php).Replace("__site_title__",$SITE_TITLE_1).Replace("__site_title_2__",$SITE_TITLE_2).Replace("rprj-db-server",$MYSQL_APP).Replace("rprj-db-db",$MYSQL_DB).Replace("rprj-db-pwd",$MYSQL_PASSWORD)
Write-Output $config_local | Out-File -Encoding "ASCII" ..\php\config_local.php

$IMG_EXISTS=ImageExists($RPRJ_IMG)
if($IMG_EXISTS) {
 Write-Output "* Image $RPRJ_IMG exists"
} else {
 Write-Output "* Creating image $RPRJ_IMG"
 docker build -f Dockerfile_dev -t $RPRJ_IMG .
}

# #### PHP
$PHP_EXISTS=ContainerExists($PHP_APP)
#Write-Output $PHP_EXISTS
if($PHP_EXISTS) {
    Write-Output "* Container $PHP_APP exists"
    docker container start $PHP_APP
} else {
    Write-Output "* Creating container $PHP_APP"

    docker run -p 8080:80 --name ${PHP_APP} `
        -v ${PRJ_HOME}/php:/var/www/html -v ${PRJ_HOME}/files:/var/www/html/files -v ${PRJ_HOME}/files:/var/www/html/mng/files `
        --link ${MYSQL_APP}:mysql `
        -d ${RPRJ_IMG}
    if(-not $?) {
        exit 1
    }
    # Init DB
    Write-Host -NoNewline "Init DB"
    docker exec -it $PHP_APP bash -c "cd /var/www/html/mng/ ; php db_update_do.php" >> .\dev_docker.log
    $retVal=$LASTEXITCODE
    while($retVal -ne 0) {
        Write-Host -NoNewline "."
        Start-Sleep -Seconds 2
        docker exec -it $PHP_APP bash -c "cd /var/www/html/mng/ ; php db_update_do.php" >> .\dev_docker.log
        $retVal=$LASTEXITCODE
    }
    docker exec -it $MYSQL_APP mysql -uroot --password=${MYSQL_PASSWORD} -e "show tables;" $MYSQL_DB
}


Write-Output "Access mysql with: docker exec -it $MYSQL_APP mysql -p$MYSQL_PASSWORD"
Write-Output "Interact with the containers with:"
Write-Output " docker exec -it $MYSQL_APP bash"
Write-Output " docker exec -it $PHP_APP bash"
Write-Output "Point your browser to: http://localhost:8080/"
Write-Output "Initialize DB with: http://localhost:8080/mng/db_update.php"
Read-Host  "Press any key to continue... "
Write-Output ""

docker container stop $PHP_APP
docker container stop $MYSQL_APP

Write-Output "docker container rm $PHP_APP"
Write-Output "docker container rm $MYSQL_APP"
