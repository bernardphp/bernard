<?php

namespace Bernard\Tests;

use Bernard\Producer;
use Bernard\Message\DefaultMessage;
use Bernard\QueueFactory\InMemoryFactory;

class ProducerTest extends \PHPUnit_Framework_TestCase
{
    public function testItDelegatesMessagesToQueue()
    {
        $queues = new InMemoryFactory;
        $message = new DefaultMessage('MyQueue');

        $producer = new Producer($queues);
        $producer->produce($message);

        $envelope = $queues->create('my-queue')->dequeue();

        $this->assertSame($message, $envelope->getMessage());
    }

    public function testQueueIsCreatedFromMessageNameOrParam()
    {
        $queues = new InMemoryFactory;
        $message = new DefaultMessage('MyQueue');

        $producer = new Producer($queues);
        $producer->produce($message);
        $this->assertTrue($queues->exists('my-queue'), "Queue automatically created");

        $this->assertFalse($queues->exists('other-queue'), "Other queue not existing before producer call");
        $producer->produce($message, 'other-queue');
        $this->assertTrue($queues->exists('other-queue'), "Other queue is created");
    }
}
