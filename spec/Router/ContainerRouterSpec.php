<?php

namespace spec\Bernard\Router;

use Bernard\Envelope;
use Bernard\Exception\ReceiverNotFoundException;
use Bernard\Message;
use Bernard\Router;
use Bernard\Router\ContainerRouter;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface {}

class ContainerRouterSpec extends ObjectBehavior
{
    function let(ContainerInterface $container)
    {
        $this->beConstructedWith($container);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ContainerRouter::class);
    }

    function it_is_a_router()
    {
        $this->shouldImplement(Router::class);
    }

    function it_maps_an_envelope(Message $message, ContainerInterface $container)
    {
        $this->beConstructedWith(
            $container,
            [
                'message' => 'my.service',
            ]
        );

        $message->getName()->willReturn('message');

        $container->has('my.service')->willReturn(true);
        $container->get('my.service')->willReturn(function () {});

        $envelope = new Envelope($message->getWrappedObject());

        $this->map($envelope)->shouldBeCallable();
    }

    function it_throws_an_exception_when_envelope_cannot_be_mapped(Message $message, ContainerInterface $container)
    {
        $message->getName()->willReturn('message');

        $container->has('my.service')->willReturn(true);
        $container->get('my.service')->willThrow(NotFoundException::class);

        $envelope = new Envelope($message->getWrappedObject());

        $this->shouldThrow(ReceiverNotFoundException::class)->duringMap($envelope);
    }
}
