<?php

namespace Bernard\Tests;

use Bernard\Consumer;
use Bernard\Queue\InMemoryQueue;
use Bernard\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\Router\SimpleRouter;
use Bernard\Middleware\MiddlewareBuilder;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->router = new SimpleRouter;
        $this->middleware = new MiddlewareBuilder;
        $this->consumer = new Consumer($this->router, $this->middleware);
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

        $this->assertFalse($this->consumer->tick($queue, array(
            'max-runtime' => -1 * PHP_INT_MAX,
        )));
    }

    public function testNoEnvelopeInQueue()
    {
        $queue = new InMemoryQueue('queue');
        $this->assertTrue($this->consumer->tick($queue));
    }

    public function testEnvelopeIsAcknowledged()
    {
        $service = new Fixtures\Service();
        $envelope = new Envelope(new DefaultMessage('ImportUsers'));

        $this->router->add('ImportUsers', $service);

        $queue = $this->getMock('Bernard\Queue');
        $queue->expects($this->once())->method('dequeue')->will($this->returnValue($envelope));
        $queue->expects($this->once())->method('acknowledge')->with($this->equalTo($envelope));

        $this->consumer->tick($queue);

        $this->assertTrue($service::$importUsers);
    }

    /**
     * @group debug
     */
    public function testEnvelopeWillBeInvoked()
    {
        $service = new Fixtures\Service();

        $this->router->add('ImportUsers', $service);

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue(new Envelope(new DefaultMessage('ImportUsers')));

        $this->consumer->tick($queue);

        $this->assertTrue($service::$importUsers);
    }
}
