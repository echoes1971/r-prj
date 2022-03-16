
docker-compose up
docker-compose up -d

# This for developing
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up

docker-compose up rprj-db

docker-compose stop

docker-compose stop rprj-app
docker-compose rm -f rprj-app
docker-compose up rprj-app

docker-compose up --build rprj-web
docker-compose stop rprj-web
docker-compose rm -f rprj-web
docker-compose up rprj-web


docker-compose stop rprj-db
docker-compose rm -f rprj-db
docker-compose up rprj-db

# Debugging

docker exec -it r-prj_rprj-app_1 sh


# Refresh the sources
rm -rf build ; mkdir build ; cp -R ../php/* ./build/

# to remove both containers AND volumes used
docker-compose down --volumes
# remove all
docker-compose down -v --remove-orphans --rmi local
docker-compose down -v --remove-orphans --rmi local ; sudo rm -rf mariadb/* files/*
# docker-compose down -v --remove-orphans --rmi local ; sudo rm -rf mariadb/* files/* ; git checkout -- php/config_local.php php/mng/db_update_do.php


# Docker stuff
docker volume prune
docker image prune



# Debugging rprj_web

docker build -f Dockerfile_web -t test_img_web .
docker run -it test_img_web ls
docker run -it --entrypoint sh test_img_web
docker run -it --entrypoint bash test_img_web
