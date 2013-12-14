<?php

namespace spec\Bernard\Middleware;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MiddlewareBuilderSpec extends ObjectBehavior
{
    /**
     * @param Bernard\Middleware $middleware
     */
    function it_have_factories_in_constructor($middleware)
    {
        $factory = function ($middleware) {
            return $middleware;
        };

        $this->beConstructedwith(array($factory));
        $this->build($middleware)->shouldReturn($middleware);
    }

    /**
     * @param Bernard\Middleware $middleware
     */
    function it_is_able_to_unshift($middleware)
    {
        $factory1 = function ($middleware) { return '1'; };
        $factory2 = function ($middleware) { return '2'; };

        $this->push($factory2);
        $this->unshift($factory1);

        $this->build($middleware)->shouldReturn('1');
    }

    function it_only_allows_callable()
    {
        $this->shouldThrow('InvalidArgumentException', 'Argument must be a callable.')
            ->duringPush('this is certainly not a callable.');

        $this->shouldNotThrow('InvalidArgumentException')->duringPush(function () {});
        $this->shouldNotThrow('InvalidArgumentException')->duringPush('var_dump');
    }
}
