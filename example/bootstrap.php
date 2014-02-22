<?php

use Bernard\Consumer;
use Bernard\EventListener;
use Bernard\Message;
use Bernard\Producer;
use Bernard\QueueFactory\PersistentFactory;
use Bernard\Router\SimpleRouter;
use Bernard\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * This file contains helper methods for the examples. See example/$driver.php
 * for how to initiate the driver. Also the helper methods can be used as
 * guidance if you are using Bernard outside a framework or you are developing
 * a plugin to a framework.
 */

if (file_exists($autoloadFile = __DIR__ . '/../vendor/autoload.php') || file_exists($autoloadFile = __DIR__ . '/../../../autoload.php')) {
    require $autoloadFile;
}
require __DIR__ . '/EchoTimeService.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

function get_serializer() {
    return new Serializer;
}

function get_event_dispatcher() {
    $dispatcher = new EventDispatcher;
    $dispatcher->addSubscriber(new EventListener\ErrorLogSubscriber);
    $dispatcher->addSubscriber(new EventListener\FailureSubscriber(get_queue_factory()));

    return $dispatcher;
}

function get_queue_factory() {
    return new PersistentFactory(get_driver(), get_serializer());
}

function get_producer() {
    return new Producer(get_queue_factory(), get_event_dispatcher());
}

function get_receivers() {
    return new SimpleRouter(array(
        'EchoTime' => new EchoTimeService,
    ));
}

function get_consumer() {
    return new Consumer(get_receivers(), get_event_dispatcher());
}

function produce() {
    $producer = get_producer();

    while (true) {
        $producer->produce(new Message\DefaultMessage('EchoTime', array(
            'time' => time(),
        )));

        usleep(rand(100, 1000));
    }
}

function consume() {
    $queues   = get_queue_factory();
    $consumer = get_consumer();

    $consumer->consume($queues->create('echo-time'));
}

function main() {
    if (!isset($_SERVER['argv'][1])) {
        die('You must provide an argument of either "consume" or "produce"');
    }

    if ($_SERVER['argv'][1] == 'produce') {
        produce();
    }

    if ($_SERVER['argv'][1] == 'consume') {
        consume();
    }
}

// Run this diddy
main();
