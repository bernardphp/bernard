<?php

namespace spec\Bernard;

use Bernard\Message;
use PhpSpec\ObjectBehavior;

class UtilSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Util');
    }

    function it_guesses_queue_name(Message $message)
    {
        $message->getName()->willReturn('SendNewsletter');

        $this->guessQueue($message)->shouldReturn('send-newsletter');
    }
}
