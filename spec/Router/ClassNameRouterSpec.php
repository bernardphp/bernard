<?php

namespace spec\Bernard\Router;

use Bernard\Envelope;
use PhpSpec\ObjectBehavior;

class ClassNameRouterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            'Bernard\\Message' => function() {},
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\\Router\\ClassNameRouter');
    }

    function it_is_a_router()
    {
        $this->shouldImplement('Bernard\\Router');
    }

    function it_maps_an_envelope(Envelope $envelope)
    {
        $envelope->getClass()->willReturn('Bernard\\Message\\DefaultMessage');

        $this->map($envelope)->shouldBeCallable();
    }

    function it_throws_an_exception_when_envelope_cannot_be_mapped(Envelope $envelope)
    {
        $envelope->getClass()->willReturn('Bernard\\Producer');

        $this->shouldThrow('Bernard\\Exception\\ReceiverNotFoundException')->duringMap($envelope);
    }
}
