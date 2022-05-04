<?php

declare(strict_types=1);

namespace Bernard\Tests\Queue;

use Bernard\Envelope;
use Bernard\Queue\PersistentQueue;

class PersistentQueueTest extends AbstractQueueTest
{
    protected function setUp(): void
    {
        $this->driver = $this->createMock('Bernard\Driver');
        $this->serializer = $this->createMock('Bernard\Serializer');
    }

    public function testEnqueue(): void
    {
        $envelope = new Envelope($this->createMock('Bernard\Message'));

        $this->serializer->expects($this->once())->method('serialize')->with($this->equalTo($envelope))
            ->willReturn('serialized message');
        $this->driver->expects($this->once())->method('pushMessage')
            ->with($this->equalTo('send-newsletter'), $this->equalTo('serialized message'));

        $queue = $this->createQueue('send-newsletter');
        $queue->enqueue($envelope);
    }

    public function testAcknowledge(): void
    {
        $envelope = new Envelope($this->createMock('Bernard\Message'));

        $this->driver->expects($this->once())->method('acknowledgeMessage')
            ->with($this->equalTo('send-newsletter'), $this->equalTo('receipt'));

        $this->driver->expects($this->once())->method('popMessage')->with($this->equalTo('send-newsletter'))
            ->willReturn(new \Bernard\Driver\Message('message', 'receipt'));

        $this->serializer->expects($this->once())->method('unserialize')
            ->willReturn($envelope);

        $queue = $this->createQueue('send-newsletter');
        $envelope = $queue->dequeue();
        $queue->acknowledge($envelope);
    }

    public function testAcknowledgeOnlyIfReceipt(): void
    {
        $envelope = new Envelope($this->createMock('Bernard\Message'));

        $this->driver->expects($this->never())->method('acknowledgeMessage');

        $queue = $this->createQueue('send-newsletter');
        $queue->acknowledge($envelope);
    }

    public function testCount(): void
    {
        $this->driver->expects($this->once())->method('countMessages')->with($this->equalTo('send-newsletter'))
            ->willReturn(10);

        $queue = $this->createQueue('send-newsletter');

        $this->assertEquals(10, $queue->count());
    }

    public function testDequeue(): void
    {
        $messageWrapper = new Envelope($this->createMock('Bernard\Message'));

        $this->driver->expects($this->at(1))->method('popMessage')->with($this->equalTo('send-newsletter'))
            ->willReturn(new \Bernard\Driver\Message('serialized', null));

        $this->driver->expects($this->at(2))->method('popMessage')->with($this->equalTo('send-newsletter'))
            ->willReturn(null);

        $this->serializer->expects($this->once())->method('unserialize')->with($this->equalTo('serialized'))
            ->willReturn($messageWrapper);

        $queue = $this->createQueue('send-newsletter');

        $this->assertSame($messageWrapper, $queue->dequeue());
        $this->assertNull($queue->dequeue());
    }

    /**
     * @dataProvider peekDataProvider
     */
    public function testPeekDserializesMessages($index, $limit): void
    {
        $this->serializer->expects($this->at(0))->method('unserialize')->with($this->equalTo('message1'));
        $this->serializer->expects($this->at(1))->method('unserialize')->with($this->equalTo('message2'));
        $this->serializer->expects($this->at(2))->method('unserialize')->with($this->equalTo('message3'));

        $this->driver->expects($this->once())->method('peekQueue')->with($this->equalTo('send-newsletter'), $this->equalTo($index), $this->equalTo($limit))
            ->willReturn(['message1', 'message2', 'message3']);

        $queue = $this->createQueue('send-newsletter');
        $queue->peek($index, $limit);
    }

    public function dataClosedMethods()
    {
        $methods = parent::dataClosedMethods();
        $methods[] = ['register', []];

        return $methods;
    }

    public function peekDataProvider()
    {
        return [
            [0, 20],
            [1, 10],
            [20, 100],
        ];
    }

    protected function createQueue($name)
    {
        return new PersistentQueue($name, $this->driver, $this->serializer);
    }
}
