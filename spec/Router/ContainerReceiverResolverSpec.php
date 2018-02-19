<?php

namespace spec\Bernard\Router;

use Bernard\Envelope;
use Bernard\Message;
use Bernard\Receiver;
use Bernard\Router\ContainerReceiverResolver;
use Bernard\Router\ReceiverResolver;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;

class ContainerReceiverResolverSpec extends ObjectBehavior
{
    function let(ContainerInterface $container)
    {
        $this->beConstructedWith($container);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ContainerReceiverResolver::class);
    }

    function it_is_a_receiver_resolver()
    {
        $this->shouldHaveType(ReceiverResolver::class);
    }

    function it_accepts_a_service_receiver(ContainerInterface $container)
    {
        $container->has('my.service')->willReturn(true);

        $this->accepts('my.service')->shouldReturn(true);
    }

    function it_returns_null_when_the_service_is_not_found(Message $message, ContainerInterface $container)
    {
        $container->get('my.service')->willThrow(ContainerNotFoundExceptionStub::class);

        $this->resolve('my.service', new Envelope($message->getWrappedObject()))->shouldReturn(null);
    }

    function it_returns_the_receiver_when_it_is_already_a_receiver(Message $message, Receiver $receiver, ContainerInterface $container)
    {
        $container->get('my.service')->willReturn($receiver);

        $this->resolve('my.service', new Envelope($message->getWrappedObject()))->shouldReturn($receiver);
    }

    function it_returns_a_callable_receiver_when_the_receiver_is_callable(Message $message, ContainerInterface $container)
    {
        $container->get('my.service')->willReturn(function (Message $message) {});

        $this->resolve('my.service', new Envelope($message->getWrappedObject()))->shouldHaveType(Receiver\CallableReceiver::class);
    }

    function it_returns_a_callable_receiver_when_the_receiver_class_has_a_static_method_for_the_message(Message $message, ContainerInterface $container)
    {
        $container->get('my.service')->willReturn(ReceiverStub::class);
        $message->getName()->willReturn('StaticMessageName');

        $this->resolve('my.service', new Envelope($message->getWrappedObject()))->shouldHaveType(Receiver\CallableReceiver::class);
    }

    function it_returns_a_callable_receiver_when_the_receiver_object_has_a_method_for_the_message(Message $message, ContainerInterface $container)
    {
        $container->get('my.service')->willReturn(new ReceiverStub());
        $message->getName()->willReturn('MessageName');

        $this->resolve('my.service', new Envelope($message->getWrappedObject()))->shouldHaveType(Receiver\CallableReceiver::class);
    }

    function it_returns_a_callable_receiver_when_the_receiver_object_has_a_static_method_for_the_message(Message $message, ContainerInterface $container)
    {
        $container->get('my.service')->willReturn(new ReceiverStub());
        $message->getName()->willReturn('StaticMessageName');

        $this->resolve('my.service', new Envelope($message->getWrappedObject()))->shouldHaveType(Receiver\CallableReceiver::class);
    }
}
