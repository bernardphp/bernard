<?php

use Bernard\Driver\Doctrine\Driver;
use Doctrine\DBAL\DriverManager;

/**
 * Must be defined before including bootstrap.php
 * as this is the only custom part in the example.
 */
function get_driver() {
    $connection = DriverManager::getConnection(array(
        'dbname' => 'bernard',
        'user' => 'root',
        'password' => null,
        'host' => 'localhost',
        'driver' => 'pdo_mysql',
    ));

    $doctrineDriver = new Driver($connection);

    //Don't do this in your application. Use a database set up script instead.
    try {
        $doctrineDriver->listQueues();
    } catch (\Exception $ex) {
        $schema = new \Doctrine\DBAL\Schema\Schema();

        \Bernard\Driver\Doctrine\MessagesSchema::create($schema);

        array_map(array($connection, 'executeQuery'), $schema->toSql($connection->getDatabasePlatform()));
    }

    return $doctrineDriver;
}

require 'bootstrap.php';
