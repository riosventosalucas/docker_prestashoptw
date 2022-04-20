#!/bin/bash

PASSWORD='<PASSWORD HERE>'

/etc/init.d/mysql restart

mysql -e "UPDATE mysql.user SET Password = PASSWORD('$PASSWORD') WHERE User = 'root'"
mysql -e "DROP USER ''@'localhost'"
mysql -e "DROP USER ''@'$(hostname)'"
mysql -e "DROP DATABASE test"
mysql -e "FLUSH PRIVILEGES"

echo "UPDATE user SET plugin='mysql_native_password' WHERE User='root';" | mysql mysql
echo "FLUSH PRIVILEGES;" | mysql mysql

echo "[mysql]
user=root
password=$PASSWORD
" > /root/.my.cnf

echo "CREATE DATABASE prestashop" | mysql

mysql prestashop < /dbdata/dump.sql

chown www-data: /var/www/html -R

/etc/init.d/apache2 restart

certbot -d $DOMAIN

tail -f /dev/null