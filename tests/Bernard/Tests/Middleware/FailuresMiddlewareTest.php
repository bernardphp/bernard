<?php

namespace Bernard\Tests\Middleware;

use Bernard\QueueFactory\InMemoryFactory;
use Bernard\Middleware\FailuresMiddleware;

class FailuresMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->next = $this->getMock('Bernard\Middleware');
        $this->queues = new InMemoryFactory;

        $this->envelope = $this->getMockBuilder('Bernard\Envelope')
            ->disableOriginalConstructor()->getMock();
        $this->envelope->expects($this->any())->method('getName')->will($this->returnValue('SendNewsletter'));

        $this->middleware = new FailuresMiddleware($this->next, $this->queues);

    }

    public function testNextIsCalled()
    {
        $this->next->expects($this->once())->method('call')->with($this->envelope);

        $this->middleware->call($this->envelope);
    }

    public function testFailedMessagesAreMovedToFailed()
    {
        $this->next->expects($this->once())->method('call')->will($this->throwException(new \Exception()));

        try {
            $this->middleware->call($this->envelope);
        } catch (\Exception $e) {
            // it bubbles the exceptions.
        }

        $this->assertCount(1, $this->queues->create('failed'));
        $this->assertSame($this->envelope, $this->queues->create('failed')->dequeue());
    }
}
