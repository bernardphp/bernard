<?php

namespace spec\Bernard\Exception;

use Bernard\Exception;
use Bernard\Exception\InvalidOperationException;
use PhpSpec\ObjectBehavior;

class InvalidOperationExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InvalidOperationException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType(Exception::class);
    }
}
