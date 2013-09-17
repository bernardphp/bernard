<?php

namespace Bernard\Tests;

use Bernard\Middleware\MiddlewareBuilder;
use Bernard\Producer;
use Bernard\Message\DefaultMessage;
use Bernard\QueueFactory\InMemoryFactory;

class ProducerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->queues = new InMemoryFactory;
        $this->producer = new Producer($this->queues, new MiddlewareBuilder);
    }

    public function testItDelegatesMessagesToQueue()
    {
        $message = new DefaultMessage('SendNewsletter');

        $this->producer->produce($message);

        $envelope = $this->queues->create('send-newsletter')->dequeue();

        $this->assertSame($message, $envelope->getMessage());
    }

    public function testItUsesGivenQueueName()
    {
        $message = new DefaultMessage('SendNewsletter');

        $this->producer->produce($message, 'something-else');

        $envelope = $this->queues->create('something-else')->dequeue();

        $this->assertSame($message, $envelope->getMessage());
    }
}
