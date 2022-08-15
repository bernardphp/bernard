<?php

namespace Bernard\Tests\Event;

use Bernard\Envelope;
use Bernard\Event\EnvelopeEvent;
use Bernard\Message;

class EnvelopeEventTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $message = $this->getMockBuilder(Message::class)->disableOriginalConstructor()
            ->getMock();
        $this->envelope = new Envelope($message);
        $this->queue = $this->createMock('Bernard\Queue');
    }

    public function testIsEvent()
    {
        $this->assertInstanceOf('Symfony\Contracts\EventDispatcher\Event', new EnvelopeEvent($this->envelope, $this->queue));
    }

    public function hasEnvelopeAndQueue()
    {
        $event = new EnvelopeEvent($this->envelope, $this->queue);

        $this->assertSame($this->envelope, $event->getEnvelope());
        $this->assertSame($this->queue, $event->getQueue());
    }
}
