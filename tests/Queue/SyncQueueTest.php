<?php

namespace Bernard\Tests\Queue;

use Bernard\Envelope;
use Bernard\Message;
use Bernard\Message\PlainMessage;
use Bernard\Queue\SyncQueue;
use Bernard\Router\SimpleRouter;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SomeService
{
    public function someMethod(PlainMessage $message)
    {
        return $message->value;
    }
}

class SyncQueueTest extends AbstractQueueTest
{
    public function testSyncExecution()
    {
        $envelope = $this->getEnvelope();

        $mock = $this->getMock(SomeService::class, array('someMethod'));
        $mock->expects($this->exactly(3))
            ->method('someMethod')
            ->willReturn('some-value')
        ;
        $queue = new SyncQueue('some-service', new EventDispatcher, new SimpleRouter(array(
            'SomeMethod' => $mock,
        )));

        $queue->enqueue($envelope);
        $queue->enqueue($envelope);
        $queue->enqueue($envelope);
    }

    public function testDequeue()
    {
        $envelope = $this->getEnvelope();

        $queue = $this->createQueue('some-service');
        $queue->enqueue($envelope);
        $queue->enqueue($envelope);

        $this->assertCount(0, $queue);
        $this->assertInternalType('null', $queue->dequeue());
        $this->assertCount(0, $queue);
        $this->assertInternalType('null', $queue->dequeue());
    }

    public function testPeek()
    {
        $queue = new SyncQueue('some-service', new EventDispatcher, new SimpleRouter(array(
            'SomeMethod' => new SomeService,
        )));

        $this->assertEquals(array(), $queue->peek(0, 10));

        $queue->enqueue($envelope  = $this->getEnvelope());
        $queue->enqueue($envelope1 = $this->getEnvelope());
        $queue->enqueue($envelope2 = $this->getEnvelope());
        $queue->enqueue($envelope3 = $this->getEnvelope());

        $this->assertCount(0, $queue);
        $this->assertSame(array(), $queue->peek(1, 2));
        $this->assertCount(0, $queue);
    }

    protected function getEnvelope()
    {
        return new Envelope(new Message\PlainMessage('SomeMethod', array(
            'value' => 'some-value',
        )));
    }

    protected function createQueue($name)
    {
        return new SyncQueue($name, new EventDispatcher, new SimpleRouter(array(
            'SomeMethod' => new SomeService,
        )));
    }
}
