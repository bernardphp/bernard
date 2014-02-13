<?php

namespace Bernard\Tests;

use Bernard\Producer;
use Bernard\Message\DefaultMessage;
use Bernard\QueueFactory\InMemoryFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Bernard\Batch;

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

        $this->dispatcher->addListener('bernard.produce', function ($event) use (&$args) {
            $args = array('envelope' => $event->getEnvelope(), 'queue' => $event->getQueue());
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

    public function testItSupportsBatches()
    {
        $message1 = new DefaultMessage('SendNewsletter');
        $message2 = new DefaultMessage('SomeThingElse');

        $batch = new Batch('my-id');
        $batch->assign($message1);
        $batch->assign($message2);

        $this->producer->produce($batch);

        $this->assertCount(1, $this->queues->create('send-newsletter'));
        $this->assertCount(1, $this->queues->create('some-thing-else'));
    }

    public function testItBatchesToForcedQueue()
    {
        $message1 = new DefaultMessage('SendNewsletter');
        $message2 = new DefaultMessage('SomeThingElse');

        $batch = new Batch('my-id');
        $batch->assign($message1);
        $batch->assign($message2);

        $this->producer->produce($batch, 'high');

        $this->assertCount(2, $this->queues->create('high'));
        $this->assertCount(0, $this->queues->create('send-newsletter'));
    }

    public function testItUsesGivenQueueName()
    {
        $message = new DefaultMessage('SendNewsletter');

        $this->producer->produce($message, 'something-else');

        $envelope = $this->queues->create('something-else')->dequeue();

        $this->assertSame($message, $envelope->getMessage());
    }
}
