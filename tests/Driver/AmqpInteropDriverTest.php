<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\AmqpInteropDriver;
use Bernard\Driver\InteropDriver;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpQueue;
use Interop\Queue\PsrContext;

class AmqpInteropDriverTest extends \PHPUnit\Framework\TestCase
{
    public function testThrowIfInteropContextNotAmqpOne()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The context must be instance of "Interop\Amqp\AmqpContext"');

        new AmqpInteropDriver(new InteropDriver($this->createMock(PsrContext::class)));
    }

    public function testItImplementsDriverInterface()
    {
        $this->assertInstanceOf(
            'Bernard\Driver',
            new AmqpInteropDriver(new InteropDriver($this->createAmqpInteropContextMock()))
        );
    }

    public function testCreateQueueMethodShouldDeclareAmqpQueue()
    {
        $queue = $this->createMock(AmqpQueue::class);
        $queue
            ->expects($this->once())
            ->method('addFlag')
            ->with(AmqpQueue::FLAG_DURABLE)
        ;

        $context = $this->createAmqpInteropContextMock();
        $context
            ->expects($this->once())
            ->method('createQueue')
            ->with('theQueueName')
            ->willReturn($queue)
        ;
        $context
            ->expects($this->once())
            ->method('declareQueue')
            ->with($this->identicalTo($queue))
        ;

        $driver = new AmqpInteropDriver(new InteropDriver($context));

        $this->assertNull($driver->createQueue('theQueueName'));
    }

    public function testDeleteQueueMethodShouldCallDeleteQueueMethodOnAmqpContext()
    {
        $queue = $this->createMock(AmqpQueue::class);
        $queue
            ->expects($this->once())
            ->method('addFlag')
            ->with(AmqpQueue::FLAG_DURABLE)
        ;

        $context = $this->createAmqpInteropContextMock();
        $context
            ->expects($this->once())
            ->method('createQueue')
            ->with('theQueueName')
            ->willReturn($queue)
        ;
        $context
            ->expects($this->once())
            ->method('deleteQueue')
            ->with($this->identicalTo($queue))
        ;

        $driver = new AmqpInteropDriver(new InteropDriver($context));

        $this->assertNull($driver->removeQueue('theQueueName'));
    }

    public function testCountMessagesMethodShouldUseCountFromDeclareQueueResult()
    {
        $queue = $this->createMock(AmqpQueue::class);

        $context = $this->createAmqpInteropContextMock();
        $context
            ->expects($this->once())
            ->method('createQueue')
            ->with('theQueueName')
            ->willReturn($queue)
        ;
        $context
            ->expects($this->once())
            ->method('declareQueue')
            ->with($this->identicalTo($queue))
            ->willReturn(123)
        ;

        $driver = new AmqpInteropDriver(new InteropDriver($context));

        $this->assertSame(123, $driver->countMessages('theQueueName'));
    }

    public function testShouldAllowGetContextSetInConstructor()
    {
        $context = $this->createAmqpInteropContextMock();

        $driver = new AmqpInteropDriver(new InteropDriver($context));

        $this->assertSame($context, $driver->getContext());
    }

    /**
     * @return PsrContext|\PHPUnit_Framework_MockObject_MockObject|AmqpContext
     */
    private function createAmqpInteropContextMock()
    {
        return $this->createMock(AmqpContext::class);
    }
}
