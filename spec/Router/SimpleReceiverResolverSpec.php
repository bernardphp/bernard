<?php

namespace spec\Bernard\Router;

use Bernard\Envelope;
use Bernard\Message;
use Bernard\Receiver;
use Bernard\Router\ReceiverResolver;
use Bernard\Router\SimpleReceiverResolver;
use PhpSpec\ObjectBehavior;

class SimpleReceiverResolverSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SimpleReceiverResolver::class);
    }

    function it_is_a_receiver_resolver()
    {
        $this->shouldHaveType(ReceiverResolver::class);
    }

    function it_accepts_a_callable_receiver()
    {
        $callable = function(Message $message) {};

        $this->accepts($callable)->shouldReturn(true);
    }

    function it_accepts_a_class_receiver()
    {
        $this->accepts(ReceiverStub::class)->shouldReturn(true);
    }

    function it_accepts_an_object_receiver()
    {
        $this->accepts(new ReceiverStub())->shouldReturn(true);
    }

    function it_returns_null_when_the_receiver_is_null(Message $message)
    {
        $this->resolve(null, new Envelope($message->getWrappedObject()))->shouldReturn(null);
    }

    function it_returns_the_receiver_when_it_is_already_a_receiver(Message $message, Receiver $receiver)
    {
        $this->resolve($receiver, new Envelope($message->getWrappedObject()))->shouldReturn($receiver);
    }

    function it_returns_a_callable_receiver_when_the_receiver_is_callable(Message $message)
    {
        $callable = function(Message $message) {};
        $this->resolve($callable, new Envelope($message->getWrappedObject()))->shouldHaveType(Receiver\CallableReceiver::class);
    }

    function it_returns_a_callable_receiver_when_the_receiver_class_has_a_static_method_for_the_message(Message $message)
    {
        $message->getName()->willReturn('StaticMessageName');

        $this->resolve(ReceiverStub::class, new Envelope($message->getWrappedObject()))->shouldHaveType(Receiver\CallableReceiver::class);
    }

    function it_returns_a_callable_receiver_when_the_receiver_object_has_a_method_for_the_message(Message $message)
    {
        $message->getName()->willReturn('MessageName');

        $this->resolve(new ReceiverStub(), new Envelope($message->getWrappedObject()))->shouldHaveType(Receiver\CallableReceiver::class);
    }

    function it_returns_a_callable_receiver_when_the_receiver_object_has_a_static_method_for_the_message(Message $message)
    {
        $message->getName()->willReturn('StaticMessageName');

        $this->resolve(new ReceiverStub(), new Envelope($message->getWrappedObject()))->shouldHaveType(Receiver\CallableReceiver::class);
    }
}
