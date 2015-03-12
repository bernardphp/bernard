#!/bin/bash

if [ $TRAVIS_PHP_VERSION != "hhvm" ] && [ $TRAVIS_PHP_VERSION != "7.0" ]; then
	pyrus install pecl/redis;
	pyrus build pecl/redis;
	echo "extension=redis.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini;
fi

if [ $TRAVIS_PHP_VERSION = "hhvm" ]; then
	composer require --dev mongofill/mongofill=dev-master --no-update;
fi

composer require --dev phpspec/phpspec:~2.0 --no-update
composer install --no-progress --no-plugins
mysql -e "CREATE DATABASE bernard_test;"
psql -c 'CREATE DATABASE bernard_test;' -U postgres
