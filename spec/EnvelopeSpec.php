<?php

namespace spec\Bernard;

use Bernard\Message;
use PhpSpec\ObjectBehavior;

class EnvelopeSpec extends ObjectBehavior
{
    function let(Message $message)
    {
        $this->beConstructedWith($message);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Envelope');
    }

    function it_has_a_message(Message $message)
    {
        $this->getMessage()->shouldReturn($message);
    }

    function it_has_a_name(Message $message)
    {
        $message->getName()->willReturn('name');

        $this->getName()->shouldReturn('name');
    }

    function it_has_a_class(Message $message)
    {
        $this->getClass()->shouldReturn(get_class($message->getWrappedObject()));
    }

    function it_has_a_timestamp()
    {
        $this->getTimestamp()->shouldReturn(time());
    }
}
