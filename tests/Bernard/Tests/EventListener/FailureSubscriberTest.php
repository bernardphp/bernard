<?php

namespace Bernard\Tests\EventListener;

use Bernard\Event\RejectEnvelopeEvent;
use Bernard\EventListener\FailureSubscriber;
use Bernard\QueueFactory\InMemoryFactory;

class FailureSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->queues = new InMemoryFactory;
        $this->subscriber = new FailureSubscriber($this->queues);
    }

    public function testAcknowledgeMessageAndEnqueue()
    {
        $envelope = $this->getMockBuilder('Bernard\Envelope')
            ->disableOriginalConstructor()->getMock();

        $queue = $this->getMock('Bernard\Queue');
        $queue->expects($this->once())->method('acknowledge')->with($envelope);

        $this->subscriber->onReject(new RejectEnvelopeEvent($envelope, $queue, new \Exception));

        $this->assertCount(1, $this->queues->create('failed'));
        $this->assertSame($envelope, $this->queues->create('failed')->dequeue());
    }
}
