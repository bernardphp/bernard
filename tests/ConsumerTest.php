<?php

declare(strict_types=1);

namespace Bernard\Tests;

use Bernard\Consumer;
use Bernard\Envelope;
use Bernard\Event\EnvelopeEvent;
use Bernard\Event\PingEvent;
use Bernard\Event\RejectEnvelopeEvent;
use Bernard\Exception\ReceiverNotFoundException;
use Bernard\Message\PlainMessage;
use Bernard\Queue\InMemoryQueue;
use Bernard\Receiver;
use Bernard\Router;
use Prophecy\Prophecy\ObjectProphecy;

class ConsumerTest extends \PHPUnit\Framework\TestCase
{
    use \Prophecy\PhpUnit\ProphecyTrait;

    /**
     * @var Router|ObjectProphecy
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

    protected function setUp(): void
    {
        $this->router = $this->prophesize(Router::class);

        $this->dispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->consumer = new Consumer($this->router->reveal(), $this->dispatcher);
    }

    public function testEmitsConsumeEvent(): void
    {
        $envelope = new Envelope($message = new PlainMessage('ImportUsers'));

        $queue = $this->getMockBuilder('Bernard\Queue\InMemoryQueue')->setMethods([
            'dequeue',
        ])->setConstructorArgs(['queue'])->getMock();

        /** @var Receiver|ObjectProphecy $receiver */
        $receiver = $this->prophesize(Receiver::class);
        $receiver->receive($message)->shouldBeCalled();
        $this->router->route($envelope)->willReturn($receiver);

        $queue->expects($this->once())
            ->method('dequeue')
            ->willReturn($envelope);

        $this->dispatcher->expects($this->at(0))->method('dispatch')
            ->with(
                $this->callback(function ($parameter) use ($queue) {
                    return $parameter == 'bernard.ping' || $parameter == new PingEvent($queue);
                }),
                $this->callback(function ($parameter) use ($queue) {
                    return $parameter == 'bernard.ping' || $parameter == new PingEvent($queue);
                }),
            );

        $this->dispatcher->expects($this->at(1))->method('dispatch')
            ->with(
                $this->callback(function ($parameter) use ($envelope, $queue) {
                    return $parameter == 'bernard.invoke' || $parameter == new EnvelopeEvent($envelope, $queue);
                }),
                $this->callback(function ($parameter) use ($envelope, $queue) {
                    return $parameter == 'bernard.invoke' || $parameter == new EnvelopeEvent($envelope, $queue);
                }),
            );

        $this->dispatcher->expects($this->at(2))->method('dispatch')
            ->with(
                $this->callback(function ($parameter) use ($envelope, $queue) {
                    return $parameter == 'bernard.acknowledge' || $parameter == new EnvelopeEvent($envelope, $queue);
                }),
                $this->callback(function ($parameter) use ($envelope, $queue) {
                    return $parameter == 'bernard.acknowledge' || $parameter == new EnvelopeEvent($envelope, $queue);
                }),
            );

        $this->assertTrue($this->consumer->tick($queue));
    }

    public function testEmitsExceptionEvent(): void
    {
        $exception = new \InvalidArgumentException();

        $envelope = new Envelope($message = new PlainMessage('ImportUsers'));
        $queue = new InMemoryQueue('queue');

        /** @var Receiver|ObjectProphecy $receiver */
        $receiver = $this->prophesize(Receiver::class);
        $receiver->receive($message)->willThrow($exception);
        $this->router->route($envelope)->willReturn($receiver);

        $this->dispatcher->expects($this->at(1))->method('dispatch')
            ->with(
                $this->callback(function ($parameter) use ($envelope, $queue, $exception) {
                    return $parameter == 'bernard.reject' || $parameter == new RejectEnvelopeEvent(
                            $envelope,
                            $queue,
                            $exception
                        );
                }),
                $this->callback(function ($parameter) use ($envelope, $queue, $exception) {
                    return $parameter == 'bernard.reject' || $parameter == new RejectEnvelopeEvent(
                            $envelope,
                            $queue,
                            $exception
                        );
                }),
            );

        $this->consumer->invoke($envelope, $queue);
    }

    public function testShutdown(): void
    {
        $queue = new InMemoryQueue('queue');

        $this->consumer->shutdown();

        $this->assertFalse($this->consumer->tick($queue));
    }

    public function testPauseResume(): void
    {
        $envelope = new Envelope($message = new PlainMessage('ImportUsers'));
        $queue = new InMemoryQueue('queue');
        $queue->enqueue($envelope);

        /** @var Receiver|ObjectProphecy $receiver */
        $receiver = $this->prophesize(Receiver::class);
        $receiver->receive($message)->shouldBeCalled();
        $this->router->route($envelope)->willReturn($receiver);

        $this->consumer->pause();

        $this->assertTrue($this->consumer->tick($queue));

        $this->consumer->resume();

        $this->assertTrue($this->consumer->tick($queue));
    }

    public function testMaxRuntime(): void
    {
        $queue = new InMemoryQueue('queue');

        $this->assertFalse($this->consumer->tick($queue, [
            'max-runtime' => -1 * \PHP_INT_MAX,
        ]));
    }

    public function testNoEnvelopeInQueue(): void
    {
        $queue = new InMemoryQueue('queue');
        $this->assertTrue($this->consumer->tick($queue));
    }

    public function testEnvelopeIsAcknowledged(): void
    {
        $envelope = new Envelope($message = new PlainMessage('ImportUsers'));

        /** @var Receiver|ObjectProphecy $receiver */
        $receiver = $this->prophesize(Receiver::class);
        $receiver->receive($message)->shouldBeCalled();
        $this->router->route($envelope)->willReturn($receiver);

        $queue = $this->createMock('Bernard\Queue');
        $queue->expects($this->once())->method('dequeue')->willReturn($envelope);
        $queue->expects($this->once())->method('acknowledge')->with($this->equalTo($envelope));

        $this->consumer->tick($queue);
    }

    public function testMaxMessages(): void
    {
        $envelope1 = new Envelope($message1 = new PlainMessage('ImportUsers'));
        $envelope2 = new Envelope($message2 = new PlainMessage('ImportUsers'));
        $envelope3 = new Envelope($message3 = new PlainMessage('ImportUsers'));

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue($envelope1);
        $queue->enqueue($envelope2);
        $queue->enqueue($envelope3);

        /** @var Receiver|ObjectProphecy $receiver */
        $receiver = $this->prophesize(Receiver::class);
        $receiver->receive($message1)->shouldBeCalled();
        $receiver->receive($message2)->shouldBeCalled();
        $receiver->receive($message3)->shouldBeCalled();
        $this->router->route($envelope1)->willReturn($receiver);
        $this->router->route($envelope2)->willReturn($receiver);
        $this->router->route($envelope3)->willReturn($receiver);

        $this->assertFalse($this->consumer->tick($queue, ['max-messages' => 1]));
        $this->assertTrue($this->consumer->tick($queue));
        $this->assertTrue($this->consumer->tick($queue, ['max-messages' => 100]));
    }

    public function testStopAfterLastMessage(): void
    {
        $envelope1 = new Envelope($message1 = new PlainMessage('ImportUsers'));
        $envelope2 = new Envelope($message2 = new PlainMessage('ImportUsers'));

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue($envelope1);
        $queue->enqueue($envelope2);

        /** @var Receiver|ObjectProphecy $receiver */
        $receiver = $this->prophesize(Receiver::class);
        $receiver->receive($message1)->shouldBeCalled();
        $receiver->receive($message2)->shouldBeCalled();
        $this->router->route($envelope1)->willReturn($receiver);
        $this->router->route($envelope2)->willReturn($receiver);

        $this->assertTrue($this->consumer->tick($queue, ['stop-when-empty' => true]));
        $this->assertTrue($this->consumer->tick($queue, ['stop-when-empty' => true]));
        $this->assertFalse($this->consumer->tick($queue, ['stop-when-empty' => true]));
    }

    public function testStopOnError(): void
    {
        $this->expectException(\Bernard\Exception\ReceiverNotFoundException::class);

        $envelope = new Envelope($message = new PlainMessage('DifferentMessageKey'));

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue($envelope);

        $this->router->route($envelope)->willThrow(ReceiverNotFoundException::class);

        $this->consumer->tick($queue, ['stop-on-error' => true]);

        $this->assertEquals(1, $queue->count());
    }

    /**
     * @group debug
     */
    public function testEnvelopeWillBeInvoked(): void
    {
        $envelope = new Envelope($message = new PlainMessage('ImportUsers'));

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue($envelope);

        /** @var Receiver|ObjectProphecy $receiver */
        $receiver = $this->prophesize(Receiver::class);
        $receiver->receive($message)->shouldBeCalled();
        $this->router->route($envelope)->willReturn($receiver);

        $this->consumer->tick($queue);
    }

    /**
     * @requires PHP 7.0
     */
    public function testWillRejectDispatchOnThrowableError(): void
    {
        $this->expectException(\TypeError::class);

        $envelope = new Envelope($message = new PlainMessage('ImportReport'));

        $queue = new InMemoryQueue('send-newsletter');
        $queue->enqueue($envelope);

        /** @var Receiver|ObjectProphecy $receiver */
        $receiver = $this->prophesize(Receiver::class);
        $receiver->receive($message)->willThrow(\TypeError::class);
        $this->router->route($envelope)->willReturn($receiver);

        $this->dispatcher->expects(self::at(0))->method('dispatch')
            ->with(
                $this->callback(function ($parameter) use ($queue) {
                    return $parameter == 'bernard.ping' || $parameter == new PingEvent($queue);
                }),
                $this->callback(function ($parameter) use ($queue) {
                    return $parameter == 'bernard.ping' || $parameter == new PingEvent($queue);
                }),
            );
        $this->dispatcher->expects(self::at(1))->method('dispatch')
            ->with(
                $this->callback(function ($parameter) use ($envelope, $queue) {
                    return $parameter == 'bernard.invoke' || $parameter == new EnvelopeEvent($envelope, $queue);
                }),
                $this->callback(function ($parameter) use ($envelope, $queue) {
                    return $parameter == 'bernard.invoke' || $parameter == new EnvelopeEvent($envelope, $queue);
                }),
            );

        $this
            ->dispatcher
            ->expects(self::at(2))
            ->method('dispatch')
            ->with(
                $this->callback(function ($parameter) use ($envelope, $queue) {
                    return $parameter == 'bernard.reject'
                        || $this->callback(function (RejectEnvelopeEvent $rejectEnvelope) {
                            $this->assertInstanceOf('TypeError', $rejectEnvelope->getException());

                            return true;
                        });
                }),
                $this->callback(function ($parameter) use ($envelope, $queue) {
                    return $parameter == 'bernard.reject'
                        || $this->callback(function (RejectEnvelopeEvent $rejectEnvelope) {
                            $this->assertInstanceOf('TypeError', $rejectEnvelope->getException());

                            return true;
                        });
                }),
            );

        $this->consumer->tick($queue, ['stop-on-error' => true]);
    }
}
