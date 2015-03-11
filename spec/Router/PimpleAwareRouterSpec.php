<?php

namespace spec\Bernard\Router;

use Bernard\Envelope;
use Pimple;
use PhpSpec\ObjectBehavior;

class PimpleAwareRouterSpec extends ObjectBehavior
{
    function let(Pimple $pimple)
    {
        $this->beConstructedWith($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Router\PimpleAwareRouter');
    }

    function it_is_a_router()
    {
        $this->shouldImplement('Bernard\Router');
    }

    function it_maps_service_to_real_callable(Envelope $envelope, Pimple $pimple)
    {
        $envelope->getName()->willReturn('Import');

        $receiver = function () {};
        $pimple->offsetGet('receiver')->willReturn($receiver);
        $pimple->offsetExists('receiver')->willReturn(true);

        $this->add('Import', 'receiver');

        $this->map($envelope)->shouldReturn($receiver);
    }

    function it_throws_an_exception_when_receiver_cannot_be_mapped(Envelope $envelope)
    {
        $envelope->getName()->willReturn('Import');

        $this->shouldThrow('Bernard\Exception\ReceiverNotFoundException')->duringMap($envelope);
    }

    function it_throws_an_exception_when_receiver_is_not_service(Envelope $envelope, Pimple $pimple)
    {
        $pimple->offsetExists('receiver')->willReturn(false);

        $this->shouldThrow('InvalidArgumentException')->duringAdd('Import', 'receiver');
    }
}
