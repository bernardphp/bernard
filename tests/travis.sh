#!/bin/bash

if [ $TRAVIS_PHP_VERSION != "hhvm" ] && [ $TRAVIS_PHP_VERSION != "7.0" ]; then
	echo "extension=mongo.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini;
fi

if [ $TRAVIS_PHP_VERSION != "hhvm" ] && [ $TRAVIS_PHP_VERSION != "5.6" ]; then
	echo "extension=mongodb.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini;
fi

if [ $TRAVIS_PHP_VERSION = "hhvm" ]; then
	composer require --dev mongofill/mongofill=dev-master --no-update;
	composer update --no-progress --no-plugins;
else
    phpenv config-rm xdebug.ini;
    composer update --no-progress --no-plugins $COMPOSER_OPTS;
    echo "extension=redis.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini;
fi

mysql -e "CREATE DATABASE bernard_test;"
psql -c 'CREATE DATABASE bernard_test;' -U postgres
