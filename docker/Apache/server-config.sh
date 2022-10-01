sed -i "s|#ServerName www\.example\.com:80|ServerName $HOSTNAME:80|g" /etc/httpd/conf/httpd.conf
sed -i "s|AllowOverride None|AllowOverride All|g" /etc/httpd/conf/httpd.conf

echo "NETWORKING=yes" > /etc/sysconfig/network

chkconfig httpd on
