<?php

namespace spec\Bernard\Symfony;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ContainerAwareRouterSpec extends ObjectBehavior
{
    /**
     * @param Symfony\Component\DependencyInjection\Container $container
     */
    function let($container)
    {
        $this->beConstructedWith($container);
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_throws_exception_when_cant_map($envelope, $container)
    {
        $this->shouldThrow('Bernard\Exception\ReceiverNotFoundException')->duringMap($envelope);
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_throws_exception_when_receiver_is_not_service($envelope, $container)
    {
        $container->has('receiver')->willReturn(false);

        $this->shouldThrow('InvalidArgumentException')->duringAdd('Import', 'receiver');
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_maps_service_to_real_callable($envelope, $container)
    {
        $envelope->getName()->willReturn('Import');

        $receiver = function () {};
        $container->get('receiver')->willReturn($receiver);
        $container->has('receiver')->willReturn(true);

        $this->add('Import', 'receiver');

        $this->map($envelope)->shouldReturn($receiver);
    }
}
