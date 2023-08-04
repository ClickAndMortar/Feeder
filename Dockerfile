FROM php:7.1-cli-buster

RUN apt-get update

RUN DEBIAN_FRONTEND=noninteractive apt-get install -y wget git unzip

RUN wget -q https://getcomposer.org/download/latest-1.x/composer.phar -O /usr/local/bin/composer && chmod +x /usr/local/bin/composer
