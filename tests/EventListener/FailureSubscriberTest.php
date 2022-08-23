<?php

declare(strict_types=1);

namespace Bernard\Tests\EventListener;

use Bernard\Envelope;
use Bernard\Event\RejectEnvelopeEvent;
use Bernard\EventListener\FailureSubscriber;
use Bernard\Message\PlainMessage;
use Bernard\Queue\InMemoryQueue;

class FailureSubscriberTest extends \PHPUnit\Framework\TestCase
{
    private $producer;
    private $subscriber;

    protected function setUp(): void
    {
        $this->producer = $this->getMockBuilder('Bernard\Producer')->disableOriginalConstructor()->getMock();
        $this->subscriber = new FailureSubscriber($this->producer, 'failures');
    }

    public function testAcknowledgeMessageAndEnqueue(): void
    {
        $envelope = new Envelope($message = new PlainMessage('bar'));

        $this->producer->expects($this->once())
            ->method('produce')
            ->with($message, 'failures');

        $this->subscriber->onReject(new RejectEnvelopeEvent($envelope, new InMemoryQueue('foo'), new \Exception()));
    }
}
