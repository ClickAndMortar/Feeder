FROM php:7.1-cli-buster

RUN apt-get update

RUN DEBIAN_FRONTEND=noninteractive apt-get install -y wget git unzip

# Composer épinglé et vérifié : SHA-256 recoupée entre getcomposer.org, la release GitHub et la signature GPG de Packagist.
ARG COMPOSER_VERSION=1.10.28
ARG COMPOSER_SHA256=0915af36eb01e3f0e16cd309adff7051832b9ef014e38371756804b20425cd5a
RUN wget -q -O /usr/local/bin/composer "https://getcomposer.org/download/${COMPOSER_VERSION}/composer.phar" \
    && echo "${COMPOSER_SHA256}  /usr/local/bin/composer" | sha256sum -c - \
    && chmod +x /usr/local/bin/composer
