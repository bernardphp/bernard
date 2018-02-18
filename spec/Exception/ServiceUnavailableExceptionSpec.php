<?php

namespace spec\Bernard\Exception;

use Bernard\Exception;
use Bernard\Exception\ServiceUnavailableException;
use PhpSpec\ObjectBehavior;

class ServiceUnavailableExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ServiceUnavailableException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType(Exception::class);
    }
}
