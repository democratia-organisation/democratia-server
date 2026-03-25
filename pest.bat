@echo off
:: Ce script ignore les arguments Windows inutiles et lance Pest dans Docker
docker exec -i democratia-web-1 /usr/src/server/vendor/bin/pest %*