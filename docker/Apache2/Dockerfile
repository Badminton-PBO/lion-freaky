FROM debian:bullseye
MAINTAINER Thomas Dekeyser <thomas.dekeyser@gmail.com>

RUN apt-get update \
&& apt-get -y install wget gnupg

RUN echo "deb https://packages.sury.org/php/ bullseye main" | tee /etc/apt/sources.list.d/sury-php.list \
&& wget -qO - https://packages.sury.org/php/apt.gpg | apt-key add - \
&& apt-get update \
&& apt-get -y install apache2 \
libapache2-mod-php8.1 \
php8.1-mysql \
php8.1-curl \
php8.1-xml \
php8.1-mbstring \
less \
vim \
&& apt-get clean

COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf

ADD server-config-debian.sh /tmp/server-config-debian.sh

RUN /bin/bash /tmp/server-config-debian.sh && \
rm /tmp/server-config-debian.sh

CMD service apache2 start;sleep infinity
