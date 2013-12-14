<?php

namespace spec\Bernard\QueueFactory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PersistentFactorySpec extends ObjectBehavior
{
    /**
     * @param Bernard\Driver $driver
     * @param Bernard\Serializer $serializer
     */
    function let($driver, $serializer)
    {
        $this->beConstructedWith($driver, $serializer);
    }

    function it_creates_queue_objects()
    {
        $this->create('queue1')->shouldReturnAnInstanceOf('Bernard\Queue\PersistentQueue');
    }

    function it_caches_queue_object()
    {
        $queue = $this->create('queue2');

        $this->create('queue2')->shouldReturn($queue);
        $queue->__toString()->shouldReturn('queue2');
    }
}
