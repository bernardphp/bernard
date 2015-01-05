<?php

namespace spec\Bernard\Stub;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestMessageSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Stub\TestMessage');
    }

    function it_is_a_message()
    {
        $this->shouldImplement('Bernard\Message');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('Test');
    }

    function it_guesses_the_queue_name()
    {
        $this->getQueue()->shouldReturn('test');
    }
}
