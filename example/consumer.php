<?php

use Raekke\ServiceResolver;
use Raekke\Consumer;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/EchoTimeService.php';

$services = new ServiceResolver;
$services->register('EchoTime', new EchoTimeService);

$consumer = new Consumer($services, $queues->create('failed'));
$consumer->consume($queues->create('echo-time'));
