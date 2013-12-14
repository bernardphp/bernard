<?php

namespace spec\Bernard\Middleware;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LoggerMiddlewareSpec extends ObjectBehavior
{
    /**
     * @param Bernard\Middleware $middleware
     * @param PSR\Log\LoggerInterface $logger
     */
    function let()
    {
        $this->beConstructedWith($middleware, $logger);
    }
}
