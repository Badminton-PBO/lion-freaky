FROM debian:buster
MAINTAINER Thomas Dekeyser <thomas.dekeyser@gmail.com>

RUN apt-get update \
&& apt-get -y install apache2 \
libapache2-mod-php7.2 \
php7.2-mysql \
php7.2-curl \
php7.2-xml \
less \
vim \
&& apt-get clean

COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf

ADD server-config-debian.sh /tmp/server-config-debian.sh

RUN /bin/bash /tmp/server-config-debian.sh && \
rm /tmp/server-config-debian.sh

CMD service apache2 start;sleep infinity
