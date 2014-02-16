<?php

namespace spec\Bernard;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EnvelopeSpec extends ObjectBehavior
{
    /**
     * @param Bernard\Message $message
     */
    function its_a_message_metadata_wrapper($message)
    {
        $message->getName()->willReturn('message-name');

        $this->beConstructedWith($message, 'Bernard\Message', 123);

        $this->getClass()->shouldReturn('Bernard\Message');
        $this->getTimestamp()->shouldReturn(123);
        $this->getMessage()->shouldReturn($message);
        $this->getName()->shouldReturn('message-name');
    }
}
