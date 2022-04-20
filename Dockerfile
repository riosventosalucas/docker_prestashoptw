FROM ubuntu:20.04

RUN apt update -y && apt upgrade -y

RUN apt install mariadb-server -y 

RUN DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends tzdata

RUN apt install php php-intl php-mysql php-xml php-mbstring -y

RUN apt install apache2 -y

RUN a2enmod ssl

RUN apt install letsencrypt -y

RUN apt install python3-certbot-apache -y

RUN mkdir -p /dbdata/backups 

RUN rm -f /var/www/html/*

RUN mkdir -p /var/www/html/.well-known/acme-challenge

RUN find /var/www/html -type f -exec chmod 644 {} \;

RUN find /var/www/html -type d -exec chmod 755 {} \;

RUN chown www-data: /var/www/html

ENV DOMAIN=tienda.guiait.com.ar

COPY ["helpers/", "/root/"]

COPY ["dist/tienda/", "/var/www/html/tienda/"]

COPY ["dist/dump.sql", "/dbdata/"]

WORKDIR /var/www/html/tienda