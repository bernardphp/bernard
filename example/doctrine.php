<?php

use Bernard\Driver\DoctrineDriver;
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

    return new DoctrineDriver($connection);
}

require 'bootstrap.php';
