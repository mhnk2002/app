FROM mysql:8.0.19
COPY ./dump/dbook.sql /docker-entrypoint-initdb.d/init.sql
