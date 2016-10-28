<?php

namespace spec\Bernard;

class SerializerSpec extends \PhpSpec\ObjectBehavior
{
    /**
     * @param Normalt\Normalizer\AggregateNormalizer $aggregate
     */
    function let($aggregate)
    {
        $this->beConstructedWith($aggregate);
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_serializes_normalized_envelope_into_json($envelope, $aggregate)
    {
        $aggregate->normalize($envelope, null)->willReturn(array(
            'class' => 'Bernard\\Message\\PlainMessage',
            'timestamp' => 1337,
            'message' => array('name' => 'Import', 'arguments' => array('arg1' => 'value')),
        ));

        $this->serialize($envelope)
            ->shouldReturn('{"class":"Bernard\\\\Message\\\\PlainMessage","timestamp":1337,"message":{"name":"Import","arguments":{"arg1":"value"}}}');
    }

    /**
     * @param Bernard\Envelope $envelope
     */
    function it_unserializes_into_envelope($envelope, $aggregate)
    {
        $normalized = array(
            'class' => 'Bernard\\Message\\PlainMessage',
            'timestamp' => 1337,
            'message' => array('name' => 'Import', 'arguments' => array('arg1' => 'value')),
        );

        $aggregate->denormalize($normalized, 'Bernard\Envelope', null)->willReturn($envelope);

        $this->unserialize('{"class":"Bernard\\\\Message\\\\PlainMessage","timestamp":1337,"message":{"name":"Import","arguments":{"arg1":"value"}}}')
            ->shouldReturn($envelope);
    }
}
