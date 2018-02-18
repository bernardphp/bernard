<?php

namespace spec\Bernard\Receiver;

use Bernard\Message;
use Bernard\Receiver;
use Bernard\Receiver\CallableReceiver;
use PhpSpec\ObjectBehavior;

class CallableReceiverSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(function() {});
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CallableReceiver::class);
    }

    function it_is_a_receiver()
    {
        $this->shouldHaveType(Receiver::class);
    }

    function it_receives_a_message(Message $message)
    {
        $this->beConstructedWith(function(Message $message) {
            $message->getName();
        });

        $message->getName()->shouldBeCalled();

        $this->receive($message);
    }
}
