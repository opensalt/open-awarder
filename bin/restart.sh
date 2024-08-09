#!/bin/bash

cd `dirname $0`/..

docker compose down -v
sleep 5
docker compose up -d
