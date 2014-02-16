<?php

namespace spec\Bernard\Serializer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SymfonySerializerSpec extends ObjectBehavior
{
    /**
     * @param Symfony\Component\Serializer\SerializerInterface $serializer
     */
    function let($serializer)
    {
        $this->beConstructedWith($serializer);
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_serializes_envelope_into_json($envelope, $serializer)
    {
        $serializer->serialize($envelope, 'json')->willReturn('message1')
            ->shouldBeCalled();

        $this->serialize($envelope)->shouldReturn('message1');
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_deserializes_json_into_Envelope($envelope, $serializer)
    {
        $serializer->deserialize('message1', 'Bernard\Envelope', 'json')->willReturn($envelope)
            ->shouldBeCalled();

        $this->deserialize('message1')->shouldReturn($envelope);
    }
}
