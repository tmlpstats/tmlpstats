FROM mysql:5.7

ENV MYSQL_ROOT_PASSWORD="doesntmatter" \
	MYSQL_USER="tmlpstats" \
	MYSQL_PASSWORD="doesntmatter" \
	MYSQL_DATABASE="tmlpstats_main"

ADD sql /docker-entrypoint-initdb.d/
