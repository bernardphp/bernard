<?php

require 'bootstrap.php';

use Bernard\Driver\DoctrineDriver;
use Bernard\Message\DefaultMessage;
use Bernard\Producer;
use Bernard\QueueFactory\PersistentFactory;
use Doctrine\DBAL\DriverManager;

$connection = DriverManager::getConnection(array(
    'dbname' => 'bernard',
    'user' => 'root',
    'password' => null,
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
));

$driver = new DoctrineDriver($connection);
$queues = new PersistentFactory($driver, $serializer);
$producer = new Producer($queues);

while (true) {
    $producer->produce(new DefaultMessage('EchoTime', array(
        'time' => time(),
    )));

    usleep(rand(100, 1000));
}
