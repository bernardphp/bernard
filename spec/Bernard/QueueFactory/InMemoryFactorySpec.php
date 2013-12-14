<?php

namespace spec\Bernard\QueueFactory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InMemoryFactorySpec extends ObjectBehavior
{
    function it_creates_queue_objects()
    {
        $this->create('queue1')->shouldReturnAnInstanceOf('Bernard\Queue\InMemoryQueue');
    }

    function it_caches_queue_object()
    {
        $queue = $this->create('queue2');

        $this->create('queue2')->shouldReturn($queue);
        $queue->__toString()->shouldReturn('queue2');
    }
}
