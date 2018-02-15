<?php

namespace spec\Bernard\Router;

use Bernard\Envelope;
use Bernard\Exception\ReceiverNotFoundException;
use Bernard\Message;
use Bernard\Router;
use Bernard\Router\ClassNameRouter;
use PhpSpec\ObjectBehavior;

class ClassNameRouterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            Message::class => function() {},
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ClassNameRouter::class);
    }

    function it_is_a_router()
    {
        $this->shouldImplement(Router::class);
    }

    function it_maps_an_envelope(Envelope $envelope)
    {
        $envelope->getClass()->willReturn(Message\PlainMessage::class);

        $this->map($envelope)->shouldBeCallable();
    }

    function it_throws_an_exception_when_envelope_cannot_be_mapped(Envelope $envelope)
    {
        $envelope->getClass()->willReturn(\stdClass::class);

        $this->shouldThrow(ReceiverNotFoundException::class)->duringMap($envelope);
    }
}
