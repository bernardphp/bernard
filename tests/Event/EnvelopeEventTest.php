<?php

declare(strict_types=1);

namespace Bernard\Tests\Event;

use Bernard\Envelope;
use Bernard\Event\EnvelopeEvent;
use Bernard\Message;

class EnvelopeEventTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $message = $this->getMockBuilder(Message::class)->disableOriginalConstructor()
            ->getMock();
        $this->envelope = new Envelope($message);
        $this->queue = $this->createMock('Bernard\Queue');
    }

    public function testIsEvent(): void
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\Event', new EnvelopeEvent($this->envelope, $this->queue));
    }

    public function hasEnvelopeAndQueue(): void
    {
        $event = new EnvelopeEvent($this->envelope, $this->queue);

        $this->assertSame($this->envelope, $event->getEnvelope());
        $this->assertSame($this->queue, $event->getQueue());
    }
}
