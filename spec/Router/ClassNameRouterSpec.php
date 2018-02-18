<?php

namespace spec\Bernard\Router;

use Bernard\Envelope;
use Bernard\Exception\ReceiverNotFoundException;
use Bernard\Message;
use Bernard\Receiver;
use Bernard\Router;
use Bernard\Router\ClassNameRouter;
use PhpSpec\ObjectBehavior;

class ClassNameRouterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ClassNameRouter::class);
    }

    function it_is_a_router()
    {
        $this->shouldImplement(Router::class);
    }

    function it_maps_an_envelope(Message $message)
    {
        $this->beConstructedWith([
            get_class($message->getWrappedObject()) => function() {},
        ]);

        $envelope = new Envelope($message->getWrappedObject());

        $this->map($envelope)->shouldImplement(Receiver::class);
    }

    function it_maps_an_envelope_to_a_message_parent(Message $message)
    {
        $this->beConstructedWith([
            Message::class => function() {},
        ]);

        $envelope = new Envelope($message->getWrappedObject());

        $this->map($envelope)->shouldImplement(Receiver::class);
    }

    function it_throws_an_exception_when_envelope_cannot_be_mapped(Message $message)
    {
        $envelope = new Envelope($message->getWrappedObject());

        $this->shouldThrow(ReceiverNotFoundException::class)->duringMap($envelope);
    }
}
