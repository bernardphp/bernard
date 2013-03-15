<?php

use Bernard\ServiceResolver\ObjectResolver;
use Bernard\Consumer;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/EchoTimeService.php';

$services = new ObjectResolver;
$services->register('EchoTime', new EchoTimeService);

$consumer = new Consumer($services, $queues->create('failed'));
$consumer->consume($queues->create('echo-time'), array(
    'max_retries' => 5,
));
