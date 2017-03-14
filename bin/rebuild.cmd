echo "Cleaning up and rebuilding container"
docker-compose stop
docker-compose rm --force
docker-compose build --pull local mysql

pause