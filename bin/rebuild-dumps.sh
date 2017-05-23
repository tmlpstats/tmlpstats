#!/bin/bash

docker run --rm -p 3306:3306 \
	-v $PWD/docker/mysql/redump-dumps.sh:/docker-entrypoint-initdb.d/zredump.sh \
	-v $PWD/docker/mysql/sql:/output \
	tmlpstats_mysql
