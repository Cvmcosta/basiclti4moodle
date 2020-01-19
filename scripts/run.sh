#!/bin/bash

sleep 10

echo 'Installing moodle ...'
php /var/www/html/admin/cli/install.php --dataroot=/moodledata --dbtype=mariadb --dbhost=$DB_HOST --dbname=$DB_NAME --dbpass=$DB_PASS --adminuser=$ADMIN_USER --adminpass=$ADMIN_PASS --adminemail="admin@email.com" --non-interactive --agree-license --lang=en --wwwroot="http://localhost:$PORT" --fullname="LTI4Moodle demo" --shortname="LTI4moodle"

php /db.php

echo 'Applying permissions ...'
chown -Rf root:www-data /var/www/html/


echo 'Done!'

exec /usr/sbin/apache2ctl -DFOREGROUND