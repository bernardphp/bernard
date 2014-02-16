<?php

namespace spec\Bernard\Serializer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use JMS\Serializer\SerializationContext;

class JMSSerializerSpec extends ObjectBehavior
{
    /**
     * @param JMS\Serializer\SerializerInterface $serializer
     */
    function let($serializer)
    {
        $this->beConstructedWith($serializer);
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_serializes_into_json_keeping_null_values($envelope, $serializer)
    {
        $context = SerializationContext::create()->setSerializeNull(true);

        $serializer->serialize($envelope, 'json', $context)->willReturn('message')
            ->shouldBeCalled();

        $this->serialize($envelope)->shouldReturn('message');
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_deserializes_into_envelope($envelope, $serializer)
    {
        $serializer->deserialize('message', 'Bernard\Envelope', 'json')
            ->willReturn($envelope)->shouldBeCalled();

        $this->deserialize('message')->shouldReturn($envelope);
    }
}
