<?php

namespace spec\Bernard\Middleware;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FailuresMiddlewareSpec extends ObjectBehavior
{
    /**
     * @param Bernard\Middleware $middleware
     * @param Bernard\QueueFactory $queues
     * @param Bernard\Envelope $envelope
     * @param Bernard\Queue $queue
     * @param Bernard\Queue $failed
     */
    function it_queues_messages_that_raises_exceptions_on_another_queue($middleware, $queues, $envelope, $queue, $failed)
    {
        $this->beConstructedWith($middleware, $queues);

        $queue->acknowledge($envelope)->shouldBeCalled();

        $queues->create('failed')->willReturn($failed);
        $failed->enqueue($envelope)->shouldBeCalled();

        $middleware->call($envelope, $queue)->willThrow('InvalidArgumentException');

        $this->shouldThrow('InvalidArgumentException')->duringCall($envelope, $queue);
    }
}
