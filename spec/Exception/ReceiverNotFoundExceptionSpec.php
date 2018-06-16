<?php

namespace spec\Bernard\Exception;

use Bernard\Exception;
use Bernard\Exception\ReceiverNotFoundException;
use PhpSpec\ObjectBehavior;

class ReceiverNotFoundExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ReceiverNotFoundException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType(Exception::class);
    }
}
