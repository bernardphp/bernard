<?php

namespace spec\Bernard;

use Normalt\Normalizer\AggregateNormalizer;
use Bernard\Envelope;
use PhpSpec\ObjectBehavior;

class SerializerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Serializer');
    }

    function it_serializes_normalized_envelope_into_json(Envelope $envelope, AggregateNormalizer $aggregate)
    {
        $aggregate->normalize($envelope, null)->willReturn(array(
            'class' => 'Bernard\Message\DefaultMessage',
            'timestamp' => 1337,
            'message' => array('name' => 'Import', 'arguments' => array('arg1' => 'value')),
        ));

        $this->beConstructedWith($aggregate);

        $this->serialize($envelope)
            ->shouldReturn('{"class":"Bernard\\\\Message\\\\DefaultMessage","timestamp":1337,"message":{"name":"Import","arguments":{"arg1":"value"}}}');
    }

    function it_unserializes_into_envelope(Envelope $envelope, AggregateNormalizer $aggregate)
    {
        $normalized = array(
            'class' => 'Bernard\\Message\\DefaultMessage',
            'timestamp' => 1337,
            'message' => array('name' => 'Import', 'arguments' => array('arg1' => 'value')),
        );

        $aggregate->denormalize($normalized, 'Bernard\Envelope', null)->willReturn($envelope);

        $this->beConstructedWith($aggregate);

        $this->unserialize('{"class":"Bernard\\\\Message\\\\DefaultMessage","timestamp":1337,"message":{"name":"Import","arguments":{"arg1":"value"}}}')
            ->shouldReturn($envelope);
    }
}
