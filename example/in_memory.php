<?php

// This example utilizes SplQueue and is therefor in memory only
// It will produce 20 jobs and then consume every job in the queue
// and when done it idles.

use Bernard\Consumer;
use Bernard\Message\DefaultMessage;
use Bernard\Producer;
use Bernard\ServiceResolver\ObjectResolver;
use Bernard\QueueFactory\InMemoryFactory;

require __DIR__ . '/../vendor/autoload.php';
require 'EchoTimeService.php';

$queues = new InMemoryFactory;
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
$consumer->consume($queues->create('echo-time'), null, array(
    'max-runtime' => 0.5,
));
