<?php

require __DIR__ . '/bootstrap.php';

use Raekke\Message\DefaultMessage;
use Raekke\Producer;

$producer = new Producer($queues);

while (true) {
    $producer->produce(new DefaultMessage('EchoTime', array(
        'time' => time(),
    )));

    usleep(rand(100, 1000));
}
