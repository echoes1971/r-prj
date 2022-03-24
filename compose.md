
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

# Developing on Windows

```
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
docker-compose stop rprj-app

cd rprj-app
#del .\node_modules\*
docker-compose stop rprj-app
npm install --silent
npm start

cd ..
docker-compose down -v --remove-orphans --rmi local
```



# Refresh the sources
rm -rf build ; mkdir build ; cp -R ../php/* ./build/

# to remove both containers AND volumes used
docker-compose down --volumes
# remove all
docker-compose down -v --remove-orphans --rmi local
docker-compose down -v --remove-orphans --rmi local ; sudo rm -rf mariadb/* files/*
# docker-compose down -v --remove-orphans --rmi local ; sudo rm -rf mariadb/* files/* ; git checkout -- php/config_local.php php/mng/db_update_do.php
docker-compose -f docker-compose.yml -f docker-compose.dev.yml down -v --remove-orphans --rmi local


# Docker stuff
docker volume prune
docker image prune

# rprj_db

docker exec -it r-rpj_rprj-db_1 mysql -pmysecret rproject


# Debugging rprj_web

docker-compose logs -f rprj-web


docker build -f Dockerfile_web -t test_img_web .
docker run -it test_img_web ls
docker run -it --entrypoint sh test_img_web
docker run -it --entrypoint bash test_img_web


docker exec -it r-prj_rprj-web_1 /bin/bash

