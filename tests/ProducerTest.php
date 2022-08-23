<?php

declare(strict_types=1);

namespace Bernard\Tests;

use Bernard\Message\PlainMessage;
use Bernard\Producer;
use Bernard\QueueFactory\InMemoryFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ProducerTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->queues = new InMemoryFactory();
        $this->dispatcher = new EventDispatcher();
        $this->producer = new Producer($this->queues, $this->dispatcher);
    }

    public function testDispatchesEvent(): void
    {
        $args = [];

        $this->dispatcher->addListener('bernard.produce', function ($event) use (&$args): void {
            $args = ['envelope' => $event->getEnvelope(), 'queue' => $event->getQueue()];
        });

        $message = new PlainMessage('Message');

        $this->producer->produce($message, 'my-queue');

        $this->assertSame($message, $args['envelope']->getMessage());
        $this->assertSame($this->queues->create('my-queue'), $args['queue']);
    }

    public function testItDelegatesMessagesToQueue(): void
    {
        $message = new PlainMessage('SendNewsletter');

        $this->producer->produce($message);

        $envelope = $this->queues->create('send-newsletter')->dequeue();

        $this->assertSame($message, $envelope->getMessage());
    }

    public function testItUsesGivenQueueName(): void
    {
        $message = new PlainMessage('SendNewsletter');

        $this->producer->produce($message, 'something-else');

        $envelope = $this->queues->create('something-else')->dequeue();

        $this->assertSame($message, $envelope->getMessage());
    }
}
