<?php

namespace Bernard\Tests;

use Bernard\Middleware\MiddlewareBuilder;
use Bernard\Producer;
use Bernard\Message\DefaultMessage;
use Bernard\QueueFactory\InMemoryFactory;

class ProducerTest extends \PHPUnit_Framework_TestCase
{
    public function testItDelegatesMessagesToQueue()
    {
        $queues = new InMemoryFactory;
        $message = new DefaultMessage('MyQueue');

        $producer = new Producer($queues, new MiddlewareBuilder);
        $producer->produce($message);

        $envelope = $queues->create('my-queue')->dequeue();

        $this->assertSame($message, $envelope->getMessage());
    }
}
