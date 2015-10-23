<?php

namespace Bernard\Tests\Queue;

use Bernard\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\Queue\InMemoryQueue;
use Bernard\Queue\RoundRobinQueue;

class RoundRobinQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryQueue[]
     */
    protected $queues;

    /**
     * @var RoundRobinQueue
     */
    protected $round;

    public function setUp()
    {
        $this->queues = [
            new InMemoryQueue('1'),
            new InMemoryQueue('2'),
            new InMemoryQueue('3'),
        ];

        $this->round = new RoundRobinQueue($this->queues);
    }

    public function testEnqueueWithUnrecognizedQueue()
    {
        $this->setExpectedException(
            'DomainException',
            'Unrecognized queue specified: foo'
        );

        $this->round->enqueue($this->getEnvelope('foo'));
    }

    public function testEnqueueWithRecognizedQueue()
    {
        $envelope = $this->getEnvelope('2');
        $this->round->enqueue($envelope);
        $this->assertSame($envelope, $this->round->dequeue());
    }

    public function testDequeueWithEmptyQueue()
    {
        $this->assertNull($this->round->dequeue());
    }

    public function testDequeueRoundRobin()
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

    public function testClose()
    {
        $builder = $this->getMockBuilder('Bernard\\Queue');
        $queues = [];
        for ($name = 1; $name <= 3; $name++) {
            $queue = $builder->getMock();
            $queue
                ->expects($this->any())
                ->method('__toString')
                ->will($this->returnValue((string) $name));
            $queue
                ->expects($this->once())
                ->method('close');
            $queues[] = $queue;
        }

        $round = new RoundRobinQueue($queues);
        $round->close();
    }

    public function testPeek()
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

    public function testAcknowledgeWithUnrecognizedQueue()
    {
        $this->setExpectedException(
            'DomainException',
            'Unrecognized queue specified: foo'
        );

        $this->round->acknowledge($this->getEnvelope('foo'));
    }

    public function testAcknowledgeWithRecognizedQueue()
    {
        $builder = $this->getMockBuilder('Bernard\\Queue');
        $envelope = $this->getEnvelope('2');

        $queues = [
            $queue_1 = $builder->getMock(),
            $queue_2 = $builder->getMock(),
            $queue_3 = $builder->getMock(),
        ];

        $queue_1->expects($this->any())->method('__toString')->will($this->returnValue('1'));
        $queue_1->expects($this->never())->method('acknowledge');
        $queue_2->expects($this->any())->method('__toString')->will($this->returnValue('2'));
        $queue_2->expects($this->once())->method('acknowledge')->with($envelope);
        $queue_3->expects($this->any())->method('__toString')->will($this->returnValue('3'));
        $queue_3->expects($this->never())->method('acknowledge');

        $round = new RoundRobinQueue($queues);
        $round->acknowledge($envelope);
    }

    public function testToString()
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

    public function testCount()
    {
        $this->round->enqueue($this->getEnvelope('1'));
        $this->round->enqueue($this->getEnvelope('2'));
        $this->round->enqueue($this->getEnvelope('3'));
        $this->assertSame(3, $this->round->count());
    }

    protected function getEnvelope($name)
    {
        return new Envelope(new DefaultMessage($name));
    }
}
