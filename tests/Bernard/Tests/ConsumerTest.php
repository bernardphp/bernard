<?php

namespace Bernard\Tests;

use Bernard\Consumer;
use Bernard\Queue\InMemoryQueue;
use Bernard\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\ServiceResolver\ObjectResolver;
use Bernard\Middleware\MiddlewareBuilder;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->resolver = new ObjectResolver;
        $this->middleware = new MiddlewareBuilder;
        $this->consumer = new Consumer($this->resolver, $this->middleware);
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

        $this->assertFalse($this->consumer->tick($queue, null, array(
            'max-runtime' => -1 * PHP_INT_MAX,
        )));
    }

    public function testNoEnvelopeInQueue()
    {
        $queue = new InMemoryQueue('queue');
        $this->assertTrue($this->consumer->tick($queue));
    }

    /**
     * @group debug
     */
    public function testEnvelopeWillBeInvoked()
    {
        $service = new Fixtures\Service();

        $this->resolver->register('ImportUsers', $service);

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue(new Envelope(new DefaultMessage('ImportUsers')));

        $this->consumer->tick($queue);

        $this->assertTrue($service::$importUsers);
    }
}
