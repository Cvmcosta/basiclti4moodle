#!/bin/bash

sleep 15

echo 'Installing moodle ...'
php /var/www/html/admin/cli/install.php --dataroot=/moodledata --dbtype=mariadb --dbhost=$DB_HOST --dbname=$DB_NAME --dbpass=$DB_PASS --adminuser=$ADMIN_USER --adminpass=$ADMIN_PASS --adminemail="admin@email.com" --non-interactive --agree-license --lang=en --wwwroot=$WWW_ROOT --fullname="LTI4Moodle demo" --shortname="LTI4moodle"

if [[ -z "${PROXY_SSL}" ]]; then
  echo 'PROXY_SSL not set, skipping config step ...'
else
  echo 'PROXY_SSL set, configuring ssl proxy support ...'
  php /var/www/html/admin/cli/cfg.php --name=dirroot --set=/var/www/html
  php /var/www/html/admin/cli/cfg.php --name=reverseproxy --set=true
  php /var/www/html/admin/cli/cfg.php --name=sslproxy --set=true
fi

php /db.php

echo 'Applying permissions ...'
chown -Rf root:www-data /var/www/html/


echo 'Done!'

exec /usr/sbin/apache2ctl -DFOREGROUND