cd ..
echo "Starting localdev container"
docker-compose build mysql
docker-compose up local
pause