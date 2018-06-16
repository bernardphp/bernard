<?php

namespace spec\Bernard\Message;

use Bernard\Message;
use Bernard\Message\PlainMessage;
use PhpSpec\ObjectBehavior;

class PlainMessageSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('SendNewsletter');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PlainMessage::class);
    }

    function it_is_a_message()
    {
        $this->shouldImplement(Message::class);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('SendNewsletter');
    }

    function it_has_arguments()
    {
        $this->beConstructedWith(
            'SendNewsletter',
            $args = [
                'key1' => 1,
                'key2' => [1, 2, 3, 4],
                'key3' => null,
            ]
        );

        $this->get('key1')->shouldReturn(1);
        $this->has('key1')->shouldReturn(true);
        $this->all()->shouldReturn($args);
    }
}
