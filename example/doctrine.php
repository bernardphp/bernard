<?php

require 'bootstrap.php';

use Bernard\Driver\DoctrineDriver;
use Bernard\Message\DefaultMessage;
use Bernard\Producer;
use Bernard\QueueFactory\PersistentFactory;
use Doctrine\DBAL\DriverManager;

$argv = $_SERVER['argv'];

if (!isset($argv[1])) {
    die('You must provide an argument of either "consume" or "produce"');
}

$connection = DriverManager::getConnection(array(
    'dbname' => 'bernard',
    'user' => 'root',
    'password' => null,
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
));

$driver = new DoctrineDriver($connection);
$queues = new PersistentFactory($driver, $serializer);

if ($argv[1] == 'produce') {
    $producer = new Producer($queues);

    while (true) {
        $producer->produce(new DefaultMessage('EchoTime', array(
            'time' => time(),
        )));

        usleep(rand(100, 1000));
    }
}

if ($argv[1] == 'consume') {
    require __DIR__ . '/consumer.php';
}
