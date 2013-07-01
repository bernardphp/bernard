<?php

require 'bootstrap.php';

use Bernard\Driver\IronMqDriver;
use Bernard\Message\DefaultMessage;
use Bernard\Producer;
use Bernard\QueueFactory\PersistentFactory;
use Bernard\Doctrine\MessagesSchema;

$argv = $_SERVER['argv'];

if (!isset($argv[1])) {
    die('You must provide an argument of either "consume" or "produce"');
}

if (!getenv('IRONMQ_TOKEN') || !getenv('IRONMQ_PROJECT_ID')) {
    die('Missing ENV variables. Make sure IRONMQ_TOKEN and IRONMQ_PROJECT_ID are set');
}

$ironmq = new IronMQ(array(
    'token'      => getenv('IRONMQ_TOKEN'),
    'project_id' => getenv('IRONMQ_PROJECT_ID'),
));
$driver = new IronMqDriver($ironmq);

$queues = new PersistentFactory($driver, $serializer);

if ($argv[1] == 'produce') {
    $producer = new Producer($queues);

    while (true) {
        $producer->produce(new DefaultMessage('EchoTime', array(
            'time' => time(),
        )));

        usleep(rand(100000, 1000000));
    }
}

if ($argv[1] == 'consume') {
    require __DIR__ . '/consumer.php';
}
