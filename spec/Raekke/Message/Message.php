<?php

namespace spec\Raekke\Message;

use PHPSpec2\ObjectBehavior;

class Message extends ObjectBehavior
{
    function it_extends_the_abstract_message()
    {
        $this->beConstructedWith('MyMessageName');
        $this->shouldHaveType('Raekke\Message\AbstractMessage');
    }

    function it_has_a_name()
    {
        $this->beConstructedWith('MyMessageName');

        $this->getMessageName()->shouldReturn('MyMessageName');
    }

    function it_supports_a_dynamic_number_of_properties()
    {
    }
}
