<?php

namespace spec\Bernard\Exception;

use Bernard\Exception;
use Bernard\Exception\NotImplementedException;
use PhpSpec\ObjectBehavior;

class NotImplementedExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NotImplementedException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType(Exception::class);
    }
}
