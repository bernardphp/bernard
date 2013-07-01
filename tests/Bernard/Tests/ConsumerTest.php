<?php

namespace Bernard\Tests;

use Bernard\Consumer;
use Bernard\Queue\InMemoryQueue;
use Bernard\Message\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\ServiceResolver\ObjectResolver;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->resolver = new ObjectResolver;
        $this->consumer = new Consumer($this->resolver);
    }

    public function testShutdown()
    {
        $queue = new InMemoryQueue('queue');

        $this->consumer->shutdown();

        $this->assertFalse($this->consumer->tick($queue));
    }

    public function testMaxRuntime()
    {
        $queue = new InMemoryQueue('queue');

        // Make sure max runtime is a looongtime in the past
        $this->consumer->configure(array(
            'max-runtime' => -1 * PHP_INT_MAX,
        ));

        $this->assertFalse($this->consumer->tick($queue));
    }

    public function testNoEnvelopeInQueue()
    {
        $queue = new InMemoryQueue('queue');
        $this->assertTrue($this->consumer->tick($queue));
    }

    public function testEnvelopeWillBeInvoked()
    {
        $service = new Fixtures\Service();

        $this->resolver->register('ImportUsers', $service);

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue(new Envelope(new DefaultMessage('ImportUsers')));

        $this->consumer->tick($queue);

        $this->assertTrue($service::$onImportUsers);
    }

    public function testFailedMessagesIsRequeuedAndMovedToFailed()
    {
        $service = new Fixtures\Service;
        $envelope = new Envelope(new DefaultMessage('SendNewsleter'));

        $this->resolver->register('FailSendNewsletter', $service);

        $failed = new InMemoryQueue('failed');
        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue($envelope);

        $this->assertEquals(0, $envelope->getRetries());

        $this->consumer->tick($queue, $failed);
        $this->consumer->tick($queue, $failed);
        $this->consumer->tick($queue, $failed);
        $this->consumer->tick($queue, $failed);
        $this->consumer->tick($queue, $failed);

        $this->assertEquals(5, $envelope->getRetries());
        $this->assertEquals(1, $queue->count());
        $this->assertEquals(0, $failed->count());

        $this->consumer->tick($queue, $failed);

        $this->assertEquals(0, $queue->count());
        $this->assertEquals(1, $failed->count());
        $this->assertSame($envelope, $failed->dequeue());
    }
}
