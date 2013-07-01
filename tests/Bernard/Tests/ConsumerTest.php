<?php

namespace Bernard\Tests;

use Bernard\Consumer;
use Bernard\Queue\InMemoryQueue;
use Bernard\Message\Envelope;
use Bernard\Message\DefaultMessage;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->resolver = $this->getMock('Bernard\ServiceResolver');
        $this->consumer = new Consumer($this->resolver);
    }

    public function testShutdown()
    {
        $queue = new InMemoryQueue('queue');

        $this->consumer->shutdown();

        $this->assertFalse($this->consumer->tick($queue));
    }

    public function testMaxRuntime()
    {
        $queue = new InMemoryQueue('queue');

        // Make sure max runtime is a looongtime in the past
        $this->consumer->configure(array(
            'max-runtime' => -1 * PHP_INT_MAX,
        ));

        $this->assertFalse($this->consumer->tick($queue));
    }

    public function testNoEnvelopeInQueue()
    {
        $this->resolver->expects($this->never())->method('resolve');

        $queue = new InMemoryQueue('queue');

        $this->assertTrue($this->consumer->tick($queue));
    }

    public function testEnvelopeWillBeInvoked()
    {
        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $invocator = $this->getMockBuilder('Bernard\ServiceResolver\Invocator')
            ->disableOriginalConstructor()->getMock();
        $invocator->expects($this->once())->method('invoke');

        $this->resolver->expects($this->once())->method('resolve')
            ->with($this->equalTo($envelope))->will($this->returnValue($invocator));

        $queue = new InMemoryQueue('queue');
        $queue->enqueue($envelope);

        $this->assertTrue($this->consumer->tick($queue));
        $this->assertEquals(0, $envelope->getRetries());
    }
}
