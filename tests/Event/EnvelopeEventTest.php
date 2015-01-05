<?php

namespace Bernard\Tests\Event;

use Bernard\Event\EnvelopeEvent;

class EnvelopeEventTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->envelope = $this->getMockBuilder('Bernard\Envelope')->disableOriginalConstructor()
            ->getMock();
        $this->queue = $this->getMock('Bernard\Queue');
    }

    public function testIsEvent()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\Event', new EnvelopeEvent($this->envelope, $this->queue));
    }

    public function hasEnvelopeAndQueue()
    {
        $event = new EnvelopeEvent($this->envelope, $this->queue);

        $this->assertSame($this->envelope, $event->getEnvelope());
        $this->assertSame($this->queue, $event->getQueue());
    }
}
