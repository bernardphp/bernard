<?php

namespace Bernard\Tests;

use Bernard\EventDispatcher;
use Bernard\Producer;
use Bernard\Message\DefaultMessage;
use Bernard\QueueFactory\InMemoryFactory;

class ProducerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->queues = new InMemoryFactory;
        $this->dispatcher = new EventDispatcher;
        $this->producer = new Producer($this->queues, $this->dispatcher);
    }

    public function testDispatchesEvent()
    {
        $args = array();

        $this->dispatcher->on('bernard.produce', function ($envelope, $queue) use (&$args) {
            $args = compact('envelope', 'queue');
        });

        $message = new DefaultMessage('Message');

        $this->producer->produce($message, 'my-queue');

        $this->assertSame($message, $args['envelope']->getMessage());
        $this->assertSame($this->queues->create('my-queue'), $args['queue']);
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
