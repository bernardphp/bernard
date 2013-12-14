<?php

namespace spec\Bernard\Driver;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PrefetchMessageCacheSpec extends ObjectBehavior
{
    function it_have_internal_queue_for_cached_messages()
    {
        $this->pop('queue1')->shouldReturn(null);

        $this->push('queue2', array('message'));
        $this->pop('queue2')->shouldReturn(array('message'));
        $this->pop('queue2')->shouldReturn(null);
    }
}
