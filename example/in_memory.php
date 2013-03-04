<?php

// This example utilizes SplQueue and is therefor in memory only
// It will produce 20 jobs and then consume every job in the queue
// and when done it idles.

use Raekke\Consumer;
use Raekke\Message\DefaultMessage;
use Raekke\Producer;
use Raekke\ServiceResolver\ObjectResolver;
use Raekke\QueueFactory\InMemoryQueueFactory;

require __DIR__ . '/../vendor/autoload.php';
require 'EchoTimeService.php';

$queues = new InMemoryQueueFactory();
$producer = new Producer($queues);

for ($i = 0; $i < 20;$i++) {
    $message = new DefaultMessage('EchoTime', array(
        'time' => time(),
    ));

    $producer->produce($message);
}

$resolver = new ObjectResolver;
$resolver->register('EchoTime', new EchoTimeService());

$consumer = new Consumer($resolver);
$consumer->consume($queues->create('echo-time'), array(
    'max_runtime' => 0.5,
));
