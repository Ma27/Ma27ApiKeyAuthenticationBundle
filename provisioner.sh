#!/usr/bin/env bash

# python software properties
sudo apt-get install -y software-properties-common python-software-properties

# php 7.1
apt-get update
apt-get upgrade
add-apt-repository ppa:ondrej/php
apt-get update

apt-get -y install php7.1
apt-get -y install php7.1-mysql
apt-get -y install php7.1-xdebug
apt-get -y install php7.1-xml
apt-get -y install php7.1-mbstring
apt-get -y install php7.1-zip

# composer
apt-get install -y curl
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chown vagrant /usr/local/bin/composer

# mysql
debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
apt-get update
apt-get -y install mysql-server

export MYSQL_PWD=root
echo "CREATE DATABASE IF NOT EXISTS symfony_functional;" | mysql -u root
