<?php

require 'bootstrap.php';

use Aws\Sqs\SqsClient;
use Bernard\Doctrine\MessagesSchema;
use Bernard\Driver\SqsDriver;
use Bernard\Message\DefaultMessage;
use Bernard\Producer;
use Bernard\QueueFactory\PersistentFactory;

$argv = $_SERVER['argv'];

if (!isset($argv[1])) {
    die('You must provide an argument of either "consume" or "produce"');
}

if (!getenv('ACCESS_KEY') || !getenv('SECRET_KEY') || !getenv('SQS_REGION')) {
    die('Missing ENV variables. Make sure ACCESS_KEY, SECRET_KEY and SQS_REGION are set');
}

$sqs = SqsClient::factory(array(
    'key'    => getenv('ACCESS_KEY'),
    'secret' => getenv('SECRET_KEY'),
    'region' => getenv('SQS_REGION')
));
$driver = new SqsDriver($sqs);

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
