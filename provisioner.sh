#!/usr/bin/env bash

# python software properties
sudo apt-get install -y software-properties-common python-software-properties

# php 5.5
sudo apt-get update
sudo apt-get -y install php5
sudo apt-get -y install php5-mysql
sudo apt-get -y install php5-xdebug

# composer
sudo apt-get install -y curl
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
sudo chown vagrant /usr/local/bin/composer

# mysql
debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
apt-get update
sudo apt-get -y install mysql-server
echo "CREATE DATABASE IF NOT EXISTS symfony_functional;" | mysql -u root -proot
