#!/bin/bash
#
# When building on debian 7 use:
# apt-get install build-essential php5-dev bison libxml2-dev \
#                 libcurl4-openssl-dev pkg-config libjpeg8-dev \
#                 libpng12-dev libmcrypt-dev libedit-dev
#

./buildconf --force

PREFIX=/opt/php7
SYSCONFDIR=/opt/php7/etc/php
CONFIG_PATH=/opt/php7/etc/php
SCAN_CONFIG_PATH=/opt/php7/etc/php/conf.d
EXTENSION_DIR=/opt/php7/usr/lib/modules

export EXTENSION_DIR

./configure \
    --prefix="$PREFIX" \
    --sysconfdir="$SYSCONFDIR" \
    --with-config-file-path="$CONFIG_PATH" \
    --with-config-file-scan-dir="$SCAN_CONFIG_PATH" \
    --enable-cli --enable-cgi --enable-fpm \
    --enable-short-tags \
    --enable-session \
    --enable-opcache --enable-opcache-file \
    --enable-pdo --with-pdo-mysql --with-pdo-sqlite \
    --enable-mysqlnd \
    --enable-ftp \
    --enable-mbstring \
    --enable-zip \
    --enable-libxml \
    --enable-simplexml \
    --enable-xml \
    --enable-xmlreader \
    --enable-xmlwriter \
    --enable-bcmath \
    --enable-calendar \
    --enable-ctype \
    --enable-dom \
    --enable-fileinfo \
    --enable-filter \
    --enable-shmop \
    --enable-sysvsem \
    --enable-sysvshm \
    --enable-sysvmsg \
    --enable-json \
    --enable-mbregex \
    --enable-mbstring \
    --enable-sockets \
    --enable-tokenizer \
    --enable-pcntl \
    --enable-phar \
    --enable-posix \
    --with-gd --enable-gd-native-ttf --with-jpeg-dir=/usr/lib/x86_64-linux-gnu/ \
    --with-sqlite3 \
    --with-mhash \
    --with-mcrypt \
    --with-pcre-regex \
    --with-readline \
    --with-libedit \
    --with-curl \
    --with-openssl \
    --with-zlib
