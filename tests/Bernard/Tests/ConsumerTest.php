<?php

namespace Bernard\Tests;

use Bernard\Consumer;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->resolver = $this->getMock('Bernard\ServiceResolver');
        $this->consumer = new Consumer($this->resolver);
    }

    public function testShutdown()
    {
        $queue = $this->getMock('Bernard\Queue');

        $this->consumer->shutdown();

        $this->assertFalse($this->consumer->tick($queue));
    }

    public function testMaxRuntime()
    {
        $queue = $this->getMock('Bernard\Queue');

        // Make sure max runtime is a looongtime in the past
        $this->consumer->configure(array(
            'max-runtime' => -1 * PHP_INT_MAX,
        ));

        $this->assertFalse($this->consumer->tick($queue));
    }

    public function testNoEnvelopeInQueue()
    {
        $this->resolver->expects($this->never())->method('resolve');

        $queue = $this->getMock('Bernard\Queue');
        $queue->expects($this->once())->method('dequeue')->will($this->returnValue(null));

        $this->assertTrue($this->consumer->tick($queue));
    }

    public function testEnvelopeWillBeInvoked()
    {
        $envelope = $this->getMockBuilder('Bernard\Message\Envelope')->disableOriginalConstructor()
            ->getMock();
        $envelope->expects($this->never())->method('incrementRetries');

        $invocator = $this->getMockBuilder('Bernard\ServiceResolver\Invocator')->disableOriginalConstructor()
            ->getMock();

        $invocator->expects($this->once())->method('invoke');

        $this->resolver->expects($this->once())->method('resolve')
            ->with($this->equalTo($envelope))->will($this->returnValue($invocator));

        $queue = $this->getMock('Bernard\Queue');
        $queue->expects($this->once())->method('dequeue')->will($this->returnValue($envelope));
        $queue->expects($this->never())->method('enqueue');

        $this->assertTrue($this->consumer->tick($queue));
    }
}
