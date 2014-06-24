<?php

namespace Bernard\Tests\EventListener;

use Bernard\Event\RejectEnvelopeEvent;
use Bernard\EventListener\FailureSubscriber;
use Bernard\QueueFactory\InMemoryFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;

class FailureSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->queues = new InMemoryFactory;

        $this->dispatcher = new EventDispatcher;
        $this->dispatcher->addSubscriber(new FailureSubscriber($this->queues));
    }

    public function testAcknowledgeMessageAndEnqueue()
    {
        $envelope = $this->getMockBuilder('Bernard\Envelope')
            ->disableOriginalConstructor()->getMock();

        $queue = $this->getMock('Bernard\Queue');
        $queue->expects($this->once())->method('acknowledge')->with($envelope);

        $this->dispatcher->dispatch('bernard.reject', new RejectEnvelopeEvent($envelope, $queue, new \Exception()));

        $this->assertCount(1, $this->queues->create('failed'));
        $this->assertSame($envelope, $this->queues->create('failed')->dequeue());
    }
}
