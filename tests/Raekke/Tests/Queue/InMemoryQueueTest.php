<?php

namespace Raekke\Tests\Queue;

use Raekke\Message\Envelope;
use Raekke\Queue\InMemoryQueue;

class InMemoryQueueTest extends AbstractQueueTest
{
    public function testDequeue()
    {
        $envelope = new Envelope($this->getMock('Raekke\Message\MessageInterface'));

        $queue = $this->createQueue('send-newsletter');
        $queue->enqueue($envelope);

        $this->assertCount(1, $queue);
        $this->assertSame($envelope, $queue->dequeue());
        $this->assertCount(0, $queue);
        $this->assertInternalType('null', $queue->dequeue());
    }

    public function testSlice()
    {
        $queue = new InMemoryQueue('send-newsletter');

        $this->assertEquals(array(), $queue->slice(0, 10));

        $queue->enqueue($envelope  = $this->getEnvelope());
        $queue->enqueue($envelope1 = $this->getEnvelope());
        $queue->enqueue($envelope2 = $this->getEnvelope());
        $queue->enqueue($envelope3 = $this->getEnvelope());

        $this->assertCount(4, $queue);
        $this->assertEquals(array(
            $envelope1,
            $envelope2,
        ), $queue->slice(1, 2));
        $this->assertCount(4, $queue);
    }

    protected function getEnvelope()
    {
        return new Envelope($this->getMock('Raekke\Message\MessageInterface'));
    }

    protected function createQueue($name)
    {
        return new InMemoryQueue($name);
    }
}
