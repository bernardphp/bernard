<?php

namespace Bernard\Tests;

use Bernard\Consumer;
use Bernard\Queue\InMemoryQueue;
use Bernard\Envelope;
use Bernard\Message\PlainMessage;
use Bernard\Router\SimpleRouter;
use Bernard\Event\RejectEnvelopeEvent;
use Bernard\Event\EnvelopeEvent;
use Bernard\Event\PingEvent;

class ConsumerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SimpleRouter
     */
    private $router;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dispatcher;

    /**
     * @var Consumer
     */
    private $consumer;

    public function setUp()
    {
        $this->router = new SimpleRouter;
        $this->router->add('ImportUsers', new Fixtures\Service);

        $this->dispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->consumer = new Consumer($this->router, $this->dispatcher);
    }

    public function testEmitsConsumeEvent()
    {
        $envelope = new Envelope(new PlainMessage('ImportUsers'));
        $queue = $this->getMockBuilder('Bernard\Queue\InMemoryQueue')->setMethods([
            'dequeue'
        ])->setConstructorArgs(['queue'])->getMock();

        $queue->expects($this->once())
            ->method('dequeue')
            ->willReturn($envelope);

        $this->dispatcher->expects($this->at(0))->method('dispatch')
            ->with('bernard.ping', new PingEvent($queue));

        $this->dispatcher->expects($this->at(1))->method('dispatch')
            ->with('bernard.invoke', new EnvelopeEvent($envelope, $queue));

        $this->dispatcher->expects($this->at(2))->method('dispatch')
            ->with('bernard.acknowledge', new EnvelopeEvent($envelope, $queue));

        $this->assertTrue($this->consumer->tick($queue));
    }

    public function testEmitsExceptionEvent()
    {
        $exception = new \InvalidArgumentException();

        $this->router->add('ImportUsers', function () use ($exception) {
            throw $exception;
        });

        $envelope = new Envelope(new PlainMessage('ImportUsers'));
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

    public function testPauseResume()
    {
        $service = new Fixtures\Service();

        $this->router->add('ImportUsers', $service);

        $queue = new InMemoryQueue('queue');
        $queue->enqueue(new Envelope(new PlainMessage('ImportUsers')));

        $this->consumer->pause();

        $this->assertTrue($this->consumer->tick($queue));
        $this->assertFalse($service->importUsers);

        $this->consumer->resume();

        $this->assertTrue($this->consumer->tick($queue));
        $this->assertTrue($service->importUsers);
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
        $envelope = new Envelope(new PlainMessage('ImportUsers'));

        $this->router->add('ImportUsers', $service);

        $queue = $this->createMock('Bernard\Queue');
        $queue->expects($this->once())->method('dequeue')->will($this->returnValue($envelope));
        $queue->expects($this->once())->method('acknowledge')->with($this->equalTo($envelope));

        $this->consumer->tick($queue);

        $this->assertTrue($service->importUsers);
    }

    public function testMaxMessages()
    {
        $this->router->add('ImportUsers', new Fixtures\Service);

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue(new Envelope(new PlainMessage('ImportUsers')));
        $queue->enqueue(new Envelope(new PlainMessage('ImportUsers')));
        $queue->enqueue(new Envelope(new PlainMessage('ImportUsers')));

        $this->assertFalse($this->consumer->tick($queue, array('max-messages' => 1)));
        $this->assertTrue($this->consumer->tick($queue));
        $this->assertTrue($this->consumer->tick($queue, array('max-messages' => 100)));
    }

    public function testStopAfterLastMessage()
    {
        $this->router->add('ImportUsers', new Fixtures\Service);

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue(new Envelope(new PlainMessage('ImportUsers')));
        $queue->enqueue(new Envelope(new PlainMessage('ImportUsers')));

        $this->assertTrue($this->consumer->tick($queue, array('stop-when-empty' => true)));
        $this->assertTrue($this->consumer->tick($queue, array('stop-when-empty' => true)));
        $this->assertFalse($this->consumer->tick($queue, array('stop-when-empty' => true)));
    }

    /**
     * @expectedException \Bernard\Exception\ReceiverNotFoundException
     */
    public function testStopOnError()
    {
        $this->router->add('ImportUsers', new Fixtures\Service);

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue(new Envelope(new PlainMessage('DifferentMessageKey')));

        $this->consumer->tick($queue, array('stop-on-error' => true));

        $this->assertEquals(1, $queue->count());
    }

    /**
     * @group debug
     */
    public function testEnvelopeWillBeInvoked()
    {
        $service = new Fixtures\Service();

        $this->router->add('ImportUsers', $service);

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue(new Envelope(new PlainMessage('ImportUsers')));

        $this->consumer->tick($queue);

        $this->assertTrue($service->importUsers);
    }

    /**
     * @requires PHP 7.0
     * @expectedException \TypeError
     */
    public function testWillRejectDispatchOnThrowableError()
    {
        $this->router->add('ImportReport', new Fixtures\Service);

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue(new Envelope(new PlainMessage('ImportReport')));

        $this->dispatcher->expects(self::at(0))->method('dispatch')->with('bernard.ping');
        $this->dispatcher->expects(self::at(1))->method('dispatch')->with('bernard.invoke');

        $this
            ->dispatcher
            ->expects(self::at(2))
            ->method('dispatch')
            ->with(
                'bernard.reject',
                self::callback(function (RejectEnvelopeEvent $rejectEnvelope) {
                    self::assertInstanceOf('TypeError', $rejectEnvelope->getException());

                    return true;
                })
            );

        $this->consumer->tick($queue, ['stop-on-error' => true]);
    }
}
