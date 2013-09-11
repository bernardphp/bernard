<?php

namespace Bernard\Tests\Middleware;

use Bernard\QueueFactory\InMemoryFactory;
use Bernard\Middleware\RetryMiddleware;

class RetryMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->next = $this->getMock('Bernard\Middleware');
        $this->queues = new InMemoryFactory;

        $this->envelope = $this->getMockBuilder('Bernard\Message\Envelope')
            ->disableOriginalConstructor()->getMock();
        $this->envelope->expects($this->any())->method('getName')->will($this->returnValue('SendNewsletter'));

        $this->middleware = new RetryMiddleware($this->next, $this->queues);

    }

    public function testNextIsCalled()
    {
        $this->next->expects($this->once())->method('call')->with($this->envelope);

        $this->middleware->call($this->envelope);
    }

    public function testExcedingRetriesAreMovedToFailed()
    {
        $this->next->expects($this->once())->method('call')->will($this->throwException(new \Exception()));
        $this->envelope->expects($this->once())->method('getRetries')->will($this->returnValue(5));

        try {
            $this->middleware->call($this->envelope);
        } catch (\Exception $e) {
            // it bubbles the exceptions.
        }

        $this->assertCount(1, $this->queues->create('failed'));
        $this->assertSame($this->envelope, $this->queues->create('failed')->dequeue());
    }

    public function testNotExceedingRetryAreReadded()
    {

        $this->next->expects($this->once())->method('call')->will($this->throwException(new \Exception()));

        $message = $this->getMock('Bernard\Message');
        $message->expects($this->once())->method('getQueue')->will($this->returnValue('send-newsletter'));

        $this->envelope->expects($this->once())->method('getRetries')->will($this->returnValue(2));
        $this->envelope->expects($this->once())->method('incrementRetries');
        $this->envelope->expects($this->any())->method('getMessage')->will($this->returnValue($message));

        try {
            $this->middleware->call($this->envelope);
        } catch (\Exception $e) {
            // it bubbles the exceptions.
        }

        $this->assertCount(0, $this->queues->create('failed'));
        $this->assertCount(1, $this->queues->create('send-newsletter'));
    }
}
