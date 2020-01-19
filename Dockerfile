FROM bitnami/minideb:latest

# Environment variables
ENV PORT=8080
ENV DB_HOST=mysql_lti4moodle
ENV DB_USER=root
ENV DB_PASS=123456
ENV DB_NAME=lti4moodle
ENV ADMIN_USER=root
ENV ADMIN_PASS=123456
ENV LTI_HOST=localhost:3000

RUN apt-get update; \
    apt-get upgrade -y; \
    apt-get install git -y; \
    apt-get install apache2 -y; \
    apt-get install npm -y; \
    a2enmod rewrite; \
    service apache2 stop; \
    apt-get install php7.3 php7.3-bcmath php7.3-bz2 php7.3-cgi php7.3-cli php7.3-common php7.3-curl php7.3-dba php7.3-dev php7.3-enchant php7.3-fpm php7.3-gd php7.3-gmp php7.3-imap php7.3-interbase php7.3-intl php7.3-json php7.3-ldap php7.3-mbstring php7.3-mysql php7.3-odbc php7.3-opcache php7.3-pgsql php7.3-phpdbg php7.3-pspell php7.3-readline php7.3-recode php7.3-snmp php7.3-soap php7.3-sqlite3 php7.3-sybase php7.3-tidy php7.3-xml php7.3-xmlrpc php7.3-xsl php7.3-zip libapache2-mod-php7.3 -y;



RUN rm -Rf /var/www/html
RUN mkdir /var/www/html
RUN chown root:www-data -R /var/www/html

# Download version 3.8 of moodle
RUN git clone -b MOODLE_38_STABLE git://git.moodle.org/moodle.git /var/www/html

RUN rm -Rf /var/www/html/mod/lti
RUN rm -Rf /var/www/html/lib/php-jwt

# Apply changes to the moodle
ADD lti /var/www/html/mod/lti
ADD php-jwt /var/www/html/lib/php-jwt


# Moodle installation steps
RUN chown -R root /var/www/html
RUN chmod -R 0755 /var/www/html
RUN find /var/www/html -type f -exec chmod 0644 {} \;

RUN mkdir /moodledata
RUN chmod 0777 /moodledata

# Add startup script
ADD scripts/run.sh /run.sh
ADD scripts/db.php /db.php
RUN chmod +x /run.sh

ENTRYPOINT [ "/run.sh" ]

EXPOSE 80