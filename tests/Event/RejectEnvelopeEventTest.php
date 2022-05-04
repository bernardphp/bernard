<?php

declare(strict_types=1);

namespace Bernard\Tests\Event;

use Bernard\Envelope;
use Bernard\Event\RejectEnvelopeEvent;
use Bernard\Message;

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

    protected function setUp(): void
    {
        $message = $this->getMockBuilder(Message::class)->disableOriginalConstructor()
            ->getMock();
        $this->envelope = new Envelope($message);
        $this->queue = $this->createMock('Bernard\Queue');
    }

    public function testExtendsEnvelopeEvent(): void
    {
        $event = new RejectEnvelopeEvent($this->envelope, $this->queue, new \Exception());

        $this->assertInstanceOf('Bernard\Event\EnvelopeEvent', $event);
    }

    public function testRetrieveException(): void
    {
        $e = new \Exception();
        $event = new RejectEnvelopeEvent($this->envelope, $this->queue, $e);

        $this->assertSame($e, $event->getException());
    }

    /**
     * @requires PHP 7.0
     */
    public function testCanContainThrowable(): void
    {
        $error = new \TypeError();
        $event = new RejectEnvelopeEvent($this->envelope, $this->queue, $error);

        self::assertSame($error, $event->getException());
    }
}
