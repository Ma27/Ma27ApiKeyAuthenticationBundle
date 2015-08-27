#!/usr/bin/env bash

# python software properties
sudo apt-get install -y software-properties-common python-software-properties

# php 5.4
sudo add-apt-repository ppa:ondrej/php5-oldstable
sudo apt-get update
sudo apt-get -y install php5
sudo apt-get -y install php5-sqlite
sudo apt-get -y install php5-xdebug

# composer
sudo apt-get install -y curl
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
sudo chown vagrant /usr/local/bin/composer

# sqlite
sudo apt-get -y install sqlite3
