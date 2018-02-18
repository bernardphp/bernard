<?php

namespace spec\Bernard\Router;

use Bernard\Envelope;
use Bernard\Exception\ReceiverNotFoundException;
use Bernard\Message;
use Bernard\Receiver;
use Bernard\Router;
use Bernard\Router\ReceiverMapRouter;
use PhpSpec\ObjectBehavior;

class ReceiverMapRouterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ReceiverMapRouter::class);
    }

    function it_is_a_router()
    {
        $this->shouldImplement(Router::class);
    }

    function it_routes_an_envelope_to_a_receiver(Message $message, Router\ReceiverResolver $receiverResolver, Receiver $receiver)
    {
        $message->getName()->willReturn('message');
        $envelope = new Envelope($message->getWrappedObject());

        $receiverResolver->accepts('receiver')->willReturn(true);
        $receiverResolver->resolve('receiver', $envelope)->willReturn($receiver);

        $this->beConstructedWith(
            [
                'message' => 'receiver',
            ],
            $receiverResolver
        );

        $this->map($envelope)->shouldReturn($receiver);
    }

    function it_throws_an_exception_when_a_receiver_is_not_accepted(Router\ReceiverResolver $receiverResolver)
    {
        $receiverResolver->accepts('receiver')->willReturn(false);

        $this->beConstructedWith(
            [
                'message' => 'receiver',
            ],
            $receiverResolver
        );

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_an_envelop_cannot_be_routed_to_a_receiver(Message $message, Router\ReceiverResolver $receiverResolver)
    {
        $message->getName()->willReturn('message');
        $envelope = new Envelope($message->getWrappedObject());

        $receiverResolver->accepts('receiver')->willReturn(true);
        $receiverResolver->resolve('receiver', $envelope)->willReturn(null);

        $this->beConstructedWith(
            [
                'message' => 'receiver',
            ],
            $receiverResolver
        );

        $this->shouldThrow(ReceiverNotFoundException::class)->duringMap($envelope);
    }
}
