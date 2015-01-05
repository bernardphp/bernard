<?php

namespace spec\Bernard\Router;

use Bernard\Envelope;
use Symfony\Component\DependencyInjection\Container;
use PhpSpec\ObjectBehavior;

class ContainerAwareRouterSpec extends ObjectBehavior
{
    function let(Container $container)
    {
        $this->beConstructedWith($container);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Router\ContainerAwareRouter');
    }

    function it_is_a_router()
    {
        $this->shouldImplement('Bernard\Router');
    }

    function it_maps_service_to_real_callable(Envelope $envelope, Container $container)
    {
        $envelope->getName()->willReturn('Import');

        $receiver = function () {};
        $container->get('receiver')->willReturn($receiver);
        $container->has('receiver')->willReturn(true);

        $this->add('Import', 'receiver');

        $this->map($envelope)->shouldReturn($receiver);
    }

    function it_throws_an_exception_when_receiver_cannot_be_mapped(Envelope $envelope)
    {
        $envelope->getName()->willReturn('Import');

        $this->shouldThrow('Bernard\Exception\ReceiverNotFoundException')->duringMap($envelope);
    }

    function it_throws_an_exception_when_receiver_is_not_service(Envelope $envelope, Container $container)
    {
        $container->has('receiver')->willReturn(false);

        $this->shouldThrow('InvalidArgumentException')->duringAdd('Import', 'receiver');
    }
}
