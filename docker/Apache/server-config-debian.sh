sed -i "s|#ServerName www\.example\.com:80|ServerName $HOSTNAME:80|g" /etc/apache2/sites-enabled/000-default.conf

a2enmod rewrite
