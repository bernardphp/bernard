<?php

declare(strict_types=1);

namespace Bernard\Tests\Queue;

use Bernard\Envelope;
use Bernard\Message\PlainMessage;
use Bernard\Queue\InMemoryQueue;
use Bernard\Queue\RoundRobinQueue;

class RoundRobinQueueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InMemoryQueue[]
     */
    protected $queues;

    /**
     * @var RoundRobinQueue
     */
    protected $round;

    protected function setUp(): void
    {
        $this->queues = [
            new InMemoryQueue('1'),
            new InMemoryQueue('2'),
            new InMemoryQueue('3'),
        ];

        $this->round = new RoundRobinQueue($this->queues);
    }

    public function testEnqueueWithUnrecognizedQueue(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Unrecognized queue specified: foo');

        $this->round->enqueue($this->getEnvelope('foo'));
    }

    public function testEnqueueWithRecognizedQueue(): void
    {
        $envelope = $this->getEnvelope('2');
        $this->round->enqueue($envelope);
        $this->assertSame($envelope, $this->round->dequeue());
    }

    public function testDequeueWithEmptyQueue(): void
    {
        $this->assertNull($this->round->dequeue());
    }

    public function testDequeueRoundRobin(): void
    {
        foreach ([
            $envelope_1_1 = $this->getEnvelope('1'),
            $envelope_1_2 = $this->getEnvelope('1'),
            $envelope_3_1 = $this->getEnvelope('3'),
        ] as $envelope) {
            $this->round->enqueue($envelope);
        }
        $this->assertSame($envelope_1_1, $this->round->dequeue());
        $this->assertSame($envelope_3_1, $this->round->dequeue());
        $this->assertSame($envelope_1_2, $this->round->dequeue());
    }

    public function testClose(): void
    {
        $builder = $this->getMockBuilder('Bernard\\Queue\\InMemoryQueue')->setMethods(['close']);
        $queues = [];
        for ($name = 1; $name <= 3; ++$name) {
            $queue = $builder->setConstructorArgs([$name])->getMock();
            $queue
                ->expects($this->once())
                ->method('close');
            $queues[] = $queue;
        }

        $round = new RoundRobinQueue($queues);
        $round->close();
    }

    public function testPeek(): void
    {
        foreach ([
            $envelope_1_1 = $this->getEnvelope('1'),
            $envelope_1_2 = $this->getEnvelope('1'),
            $envelope_3_1 = $this->getEnvelope('3'),
        ] as $envelope) {
            $this->round->enqueue($envelope);
        }
        $this->assertSame([$envelope_3_1], $this->round->peek(1, 1));
    }

    public function testAcknowledgeWithUnrecognizedQueue(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Unrecognized queue specified: foo');

        $envelope = $this->getEnvelope('foo');
        $this->round->enqueue($envelope);
        $dequeued = $this->round->dequeue($envelope);
        $this->assertSame($envelope, $dequeued);
        $this->round->acknowledge($dequeued);
    }

    public function testAcknowledgeWithRecognizedQueue(): void
    {
        $builder = $this->getMockBuilder('Bernard\\Queue\\InMemoryQueue')->setMethods(['acknowledge']);
        $envelope = $this->getEnvelope('2');

        $queues = [
            $queue_1 = $builder->setConstructorArgs(['1'])->getMock(),
            $queue_2 = $builder->setConstructorArgs(['2'])->getMock(),
            $queue_3 = $builder->setConstructorArgs(['3'])->getMock(),
        ];

        $queue_1->expects($this->never())->method('acknowledge');
        $queue_2->expects($this->once())->method('acknowledge')->with($envelope);
        $queue_3->expects($this->never())->method('acknowledge');

        $round = new RoundRobinQueue($queues);
        $round->enqueue($envelope);
        $dequeued = $round->dequeue();
        $this->assertSame($envelope, $dequeued);
        $round->acknowledge($envelope);
    }

    public function testToString(): void
    {
        $this->round->enqueue($this->getEnvelope('1'));
        $this->round->enqueue($this->getEnvelope('2'));
        $this->round->enqueue($this->getEnvelope('3'));

        $this->assertSame('1', (string) $this->round);

        $this->round->dequeue();
        $this->assertSame('2', (string) $this->round);

        $this->round->dequeue();
        $this->assertSame('3', (string) $this->round);
    }

    public function testCount(): void
    {
        $this->round->enqueue($this->getEnvelope('1'));
        $this->round->enqueue($this->getEnvelope('2'));
        $this->round->enqueue($this->getEnvelope('3'));
        $this->assertSame(3, $this->round->count());
    }

    protected function getEnvelope($name)
    {
        return new Envelope(new PlainMessage($name));
    }
}
