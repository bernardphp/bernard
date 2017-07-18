<?php

namespace Bernard\Tests\Event;

use Bernard\Event\RejectEnvelopeEvent;

class RejectEnvelopeEventTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Bernard\Envelope|\PHPUnit_Framework_MockObject_MockObject
     */
    private $envelope;

    /**
     * @var \Bernard\Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queue;

    public function setUp()
    {
        $this->envelope = $this->getMockBuilder('Bernard\Envelope')
            ->disableOriginalConstructor()->getMock();
        $this->queue = $this->createMock('Bernard\Queue');
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

    /**
     * @requires PHP 7.0
     */
    public function testCanContainThrowable()
    {
        $error = new \TypeError();
        $event = new RejectEnvelopeEvent($this->envelope, $this->queue, $error);

        self::assertSame($error, $event->getException());
    }
}
