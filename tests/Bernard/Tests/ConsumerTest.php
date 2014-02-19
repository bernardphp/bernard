<?php

namespace Bernard\Tests;

use Bernard\Consumer;
use Bernard\Queue\InMemoryQueue;
use Bernard\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\Router\SimpleRouter;
use Bernard\Event\RejectEnvelopeEvent;
use Bernard\Event\EnvelopeEvent;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->router = new SimpleRouter;
        $this->router->add('ImportUsers', new Fixtures\Service);

        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->consumer = new Consumer($this->router, $this->dispatcher);
    }

    public function testEmitsConsumeEvent()
    {
        $envelope = new Envelope(new DefaultMessage('ImportUsers'));
        $queue = new InMemoryQueue('queue');

        $this->dispatcher->expects($this->at(0))->method('dispatch')
            ->with('bernard.invoke', new EnvelopeEvent($envelope, $queue));

        $this->dispatcher->expects($this->at(1))->method('dispatch')
            ->with('bernard.acknowledge', new EnvelopeEvent($envelope, $queue));

        $this->consumer->invoke($envelope, $queue);
    }

    public function testEmitsExceptionEvent()
    {
        $exception = new \InvalidArgumentException();

        $this->router->add('ImportUsers', function () use ($exception) {
            throw $exception;
        });

        $envelope = new Envelope(new DefaultMessage('ImportUsers'));
        $queue = new InMemoryQueue('queue');

        $this->dispatcher->expects($this->at(1))->method('dispatch')
            ->with('bernard.reject', new RejectEnvelopeEvent($envelope, $queue, $exception));

        $this->consumer->invoke($envelope, $queue);
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
