<?php

namespace spec\Raekke\Message;

use PHPSpec2\ObjectBehavior;

class Serializer extends ObjectBehavior
{
    function it_encodes_a_message($message)
    {
        $message = new \Raekke\Message\Message('Import');
        $this->encode($message)->shouldReturn(serialize($message));
    }

    function it_decodes_a_message()
    {
        $message = new \Raekke\Message\Message('Import');
        $this->decode(serialize($message))->shouldReturnAnInstanceOf('Raekke\Message\Message');
    }
}
