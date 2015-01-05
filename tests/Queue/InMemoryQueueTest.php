<?php

namespace Bernard\Tests\Queue;

use Bernard\Envelope;
use Bernard\Queue\InMemoryQueue;

class InMemoryQueueTest extends AbstractQueueTest
{
    public function testDequeue()
    {
        $envelope = new Envelope($this->getMock('Bernard\Message'));

        $queue = $this->createQueue('send-newsletter');
        $queue->enqueue($envelope);

        $this->assertCount(1, $queue);
        $this->assertSame($envelope, $queue->dequeue());
        $this->assertCount(0, $queue);
        $this->assertInternalType('null', $queue->dequeue());
    }

    public function testPeek()
    {
        $queue = new InMemoryQueue('send-newsletter');

        $this->assertEquals(array(), $queue->peek(0, 10));

        $queue->enqueue($envelope  = $this->getEnvelope());
        $queue->enqueue($envelope1 = $this->getEnvelope());
        $queue->enqueue($envelope2 = $this->getEnvelope());
        $queue->enqueue($envelope3 = $this->getEnvelope());

        $this->assertCount(4, $queue);
        $this->assertSame(array(
            $envelope1,
            $envelope2,
        ), $queue->peek(1, 2));
        $this->assertCount(4, $queue);
    }

    protected function getEnvelope()
    {
        return new Envelope($this->getMock('Bernard\Message'));
    }

    protected function createQueue($name)
    {
        return new InMemoryQueue($name);
    }
}
