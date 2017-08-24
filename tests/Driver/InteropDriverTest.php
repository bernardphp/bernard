<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\InteropDriver;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpQueue;
use Interop\Queue\PsrConsumer;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrProducer;
use Interop\Queue\PsrQueue;

class InteropDriverTest extends \PHPUnit\Framework\TestCase
{
    public function testItImplementsDriverInterface()
    {
        $this->assertInstanceOf('Bernard\Driver', new InteropDriver($this->createInteropContextMock()));
    }

    public function testListQueuesMethodDoesNothingAndAlwaysReturnEmptyArray()
    {
        $driver = new InteropDriver($this->createInteropContextMock());

        $this->assertSame([], $driver->listQueues());
    }

    public function testCreateQueueMethodDoesNothingAndAlwaysReturnNull()
    {
        $driver = new InteropDriver($this->createInteropContextMock());

        $this->assertNull($driver->createQueue('aQueueName'));
    }

    public function testPushMessageMethodPublishMessageToQueueUsingInteropProducer()
    {
        $queue = $this->createMock(PsrQueue::class);
        $message = $this->createMock(PsrMessage::class);

        $producer = $this->createMock(PsrProducer::class);
        $producer
            ->expects($this->once())
            ->method('send')
            ->with($this->identicalTo($queue), $this->identicalTo($message))
        ;

        $context = $this->createInteropContextMock();
        $context
            ->expects($this->once())
            ->method('createQueue')
            ->with('theQueueName')
            ->willReturn($queue)
        ;
        $context
            ->expects($this->once())
            ->method('createMessage')
            ->with('theBody')
            ->willReturn($message)
        ;
        $context
            ->expects($this->once())
            ->method('createProducer')
            ->willReturn($producer)
        ;

        $driver = new InteropDriver($context);

        $driver->pushMessage('theQueueName', 'theBody');
    }

    public function testPopMessageReturnNullIfInteropConsumerReturnNothingOnReceive()
    {
        $queue = $this->createMock(PsrQueue::class);

        $consumer = $this->createMock(PsrConsumer::class);
        $consumer
            ->expects($this->once())
            ->method('receive')
            ->with(5000)
            ->willReturn(null)
        ;

        $context = $this->createInteropContextMock();
        $context
            ->expects($this->once())
            ->method('createQueue')
            ->with('theQueueName')
            ->willReturn($queue)
        ;
        $context
            ->expects($this->once())
            ->method('createConsumer')
            ->with($this->identicalTo($queue))
            ->willReturn($consumer)
        ;

        $driver = new InteropDriver($context);

        $this->assertNull($driver->popMessage('theQueueName'));
    }

    public function testPopMessageReturnArrayWithBodyAndInteropMessage()
    {
        $queue = $this->createMock(PsrQueue::class);
        $message = $this->createMock(PsrMessage::class);
        $message
            ->expects($this->once())
            ->method('getBody')
            ->willReturn('theBody')
        ;

        $consumer = $this->createMock(PsrConsumer::class);
        $consumer
            ->expects($this->once())
            ->method('receive')
            ->with(6789)
            ->willReturn($message)
        ;

        $context = $this->createInteropContextMock();
        $context
            ->expects($this->once())
            ->method('createQueue')
            ->with('theQueueName')
            ->willReturn($queue)
        ;
        $context
            ->expects($this->once())
            ->method('createConsumer')
            ->with($this->identicalTo($queue))
            ->willReturn($consumer)
        ;

        $driver = new InteropDriver($context);

        $this->assertSame(
            ['theBody', $message],
            $driver->popMessage('theQueueName', 6.789)
        );
    }

    public function testAcknowledgeMessage()
    {
        $queue = $this->createMock(PsrQueue::class);
        $message = $this->createMock(PsrMessage::class);

        $consumer = $this->createMock(PsrConsumer::class);
        $consumer
            ->expects($this->once())
            ->method('receive')
            ->willReturn($message)
        ;
        $consumer
            ->expects($this->once())
            ->method('acknowledge')
            ->with($this->identicalTo($message))
        ;

        $context = $this->createInteropContextMock();
        $context
            ->expects($this->once())
            ->method('createQueue')
            ->with('theQueueName')
            ->willReturn($queue)
        ;
        $context
            ->expects($this->once())
            ->method('createConsumer')
            ->with($this->identicalTo($queue))
            ->willReturn($consumer)
        ;

        $driver = new InteropDriver($context);

        $result =  $driver->popMessage('theQueueName');

        //guard
        $this->assertSame($message, $result[1]);

        $driver->acknowledgeMessage('theQueueName', $result[1]);
    }

    public function testPeekQueueMethodDoesNothingAndAlwaysReturnEmptyArray()
    {
        $driver = new InteropDriver($this->createInteropContextMock());

        $this->assertSame([], $driver->peekQueue('aQueueName'));
    }

    public function testRemoveQueueMethodDoesNothingAndAlwaysReturnNull()
    {
        $driver = new InteropDriver($this->createInteropContextMock());

        $this->assertNull($driver->removeQueue('aQueueName'));
    }

    public function testInfoMethodDoesNothingAndAlwaysReturnEmptyArray()
    {
        $driver = new InteropDriver($this->createInteropContextMock());

        $this->assertNull($driver->removeQueue('aQueueName'));
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

        $driver = new InteropDriver($context);

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

        $driver = new InteropDriver($context);

        $this->assertNull($driver->removeQueue('theQueueName'));
    }

    public function testCountMessagesMethodShouldUseCountFromAmqpDeclareQueueResult()
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

        $driver = new InteropDriver($context);

        $this->assertSame(123, $driver->countMessages('theQueueName'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AmqpContext
     */
    private function createAmqpInteropContextMock()
    {
        return $this->createMock(AmqpContext::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PsrContext
     */
    private function createInteropContextMock()
    {
        return $this->createMock(PsrContext::class);
    }
}
