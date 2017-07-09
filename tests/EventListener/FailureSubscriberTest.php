<?php

namespace Bernard\Tests\EventListener;

use Bernard\Envelope;
use Bernard\Event\RejectEnvelopeEvent;
use Bernard\EventListener\FailureSubscriber;
use Bernard\Message\DefaultMessage;
use Bernard\Queue\InMemoryQueue;

class FailureSubscriberTest extends \PHPUnit\Framework\TestCase
{
    private $producer;
    private $subscriber;

    public function setUp()
    {
        $this->producer = $this->getMockBuilder('Bernard\Producer')->disableOriginalConstructor()->getMock();
        $this->subscriber = new FailureSubscriber($this->producer, 'failures');
    }

    public function testAcknowledgeMessageAndEnqueue()
    {
        $envelope = new Envelope($message = new DefaultMessage('bar'));

        $this->producer->expects($this->once())
            ->method('produce')
            ->with($message, 'failures');

        $this->subscriber->onReject(new RejectEnvelopeEvent($envelope, new InMemoryQueue('foo'), new \Exception));

    }
}
