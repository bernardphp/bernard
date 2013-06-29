<?php

use Bernard\ServiceResolver\ObjectResolver;
use Bernard\Consumer;

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/EchoTimeService.php';

$services = new ObjectResolver;
$services->register('EchoTime', new EchoTimeService);

$consumer = new Consumer($services);
$consumer->consume($queues->create('echo-time'), $queues->create('failed'), array(
    'max_retries' => 5,
));
