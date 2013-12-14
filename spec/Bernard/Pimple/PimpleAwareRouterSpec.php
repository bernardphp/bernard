<?php

namespace spec\Bernard\Pimple;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PimpleAwareRouterSpec extends ObjectBehavior
{
    /**
     * @param Pimple $pimple
     */
    function let($pimple)
    {
        $this->beConstructedWith($pimple);
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_throws_exception_when_cant_map($envelope, $pimple)
    {
        $this->shouldThrow('Bernard\Exception\ReceiverNotFoundException')->duringMap($envelope);
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_throws_exception_when_receiver_is_not_service($envelope, $pimple)
    {
        $pimple->offsetExists('receiver')->willReturn(false);

        $this->shouldThrow('InvalidArgumentException')->duringAdd('Import', 'receiver');
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_maps_service_to_real_callable($envelope, $pimple)
    {
        $envelope->getName()->willReturn('Import');

        $receiver = function () {};
        $pimple->offsetGet('receiver')->willReturn($receiver);
        $pimple->offsetExists('receiver')->willReturn(true);

        $this->add('Import', 'receiver');

        $this->map($envelope)->shouldReturn($receiver);
    }
}
