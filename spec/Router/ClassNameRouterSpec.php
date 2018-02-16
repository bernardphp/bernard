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
            Message\PlainMessage::class => function() {},
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

    function it_maps_an_envelope()
    {
        $envelope = new Envelope(new Message\PlainMessage('SendNewsletter'));

        $this->map($envelope)->shouldBeCallable();
    }

    function it_throws_an_exception_when_envelope_cannot_be_mapped(Message $message)
    {
        $envelope = new Envelope($message->getWrappedObject());

        $this->shouldThrow(ReceiverNotFoundException::class)->duringMap($envelope);
    }
}
