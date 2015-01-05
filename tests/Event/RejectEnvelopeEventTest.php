<?php

namespace Bernard\Tests\Event;

use Bernard\Event\RejectEnvelopeEvent;

class RejectEnvelopeEventTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->envelope = $this->getMockBuilder('Bernard\Envelope')
            ->disableOriginalConstructor()->getMock();
        $this->queue = $this->getMock('Bernard\Queue');
    }

    public function testExtendsEnvelopeEvent()
    {
        $event = new RejectEnvelopeEvent($this->envelope, $this->queue, new \Exception());

        $this->assertInstanceOf('Bernard\Event\EnvelopeEvent', $event);
    }

    public function testRetrieveException()
    {
        $e = new \Exception();
        $event = new RejectEnvelopeEvent($this->envelope, $this->queue, $e);

        $this->assertSame($e, $event->getException());
    }
}
