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

    public function testMaxMessages()
    {
        $this->router->add('ImportUsers', new Fixtures\Service);

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue(new Envelope(new DefaultMessage('ImportUsers')));
        $queue->enqueue(new Envelope(new DefaultMessage('ImportUsers')));
        $queue->enqueue(new Envelope(new DefaultMessage('ImportUsers')));

        $this->assertFalse($this->consumer->tick($queue, array('max-messages' => 1)));
        $this->assertTrue($this->consumer->tick($queue));
        $this->assertTrue($this->consumer->tick($queue, array('max-messages' => 100)));
    }

    public function testRoundRobin()
    {
        $services = $envelopes = $queues = $callbacks = $invocations = [];

        for ($i = 1; $i <= 2; $i++) {
            $service = 'Service' . $i;
            $invocations[$i] = 0;
            $callbacks[$i] = function() use (&$invocations, $i) { $invocations[$i]++; };
            $this->router->add($service, $callbacks[$i]);
            $envelopes[$i] = new Envelope(new DefaultMessage($service));
            $queues[$i] = $this->getMock('Bernard\Queue');
            $queues[$i]->expects($this->at(0))->method('dequeue')->will($this->returnValue($envelopes[$i]));
            $queues[$i]->expects($this->at(1))->method('acknowledge')->with($this->equalTo($envelopes[$i]));
            $queues[$i]->expects($this->at(2))->method('dequeue')->will($this->returnValue(null));
        }

        $this->consumer->consume($queues, ['round-robin' => true]);

        $this->assertEquals($invocations[1], 1);
        $this->assertEquals($invocations[2], 1);
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
