
Set-Location ..
$PRJ_HOME = (Get-Location).Path
Set-Location docker
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
$MYSQL_EXISTS = (docker container ls -a | out-string).replace("${MYSQL_APP}-image","").replace("{$MYSQL_APP}-php","").indexof($MYSQL_APP) -gt -1
Write-Output "MYSQL_EXISTS: $MYSQL_EXISTS"
if($MYSQL_EXISTS) {
    Write-Output "* Container $MYSQL_APP exists"
    #docker container stop $MYSQL_APP
    #docker container rm $MYSQL_APP
    docker container start $MYSQL_APP
} else {
    Write-Output "* Creating container $MYSQL_APP"
    #docker container rm $MYSQL_APP
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
Write-Output "Access mysql with: docker exec -it $MYSQL_APP mysql -u root -p${MYSQL_PASSWORD}"

