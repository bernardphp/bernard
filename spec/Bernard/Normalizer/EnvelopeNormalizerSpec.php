<?php

namespace spec\Bernard\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EnvelopeNormalizerSpec extends ObjectBehavior
{
    /**
     * @param Normalt\Marshaller $marshaller
     */
    function let($marshaller)
    {
    }

    function it_is_a_normalizer_and_denormailzer()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    /**
     * @param Bernard\Envelope $envelope
     * @param Bernard\Message $message
     */
    function it_normalizes_envelope_and_delegates_message_to_marshaller($envelope, $message, $marshaller)
    {
        $envelope->getMessage()->willReturn($message);
        $envelope->getClass()->willReturn('Bernard\Message');
        $envelope->getTimestamp()->willReturn(1337);

        $marshaller->normalize($message)->willReturn(array(
            'key' => 'value',
        ));

        $this->setMarshaller($marshaller);

        $this->normalize($envelope)->shouldReturn(array(
            'class'     => 'Bernard\\Message',
            'timestamp' => 1337,
            'message'   => array('key' => 'value'),
        ));
    }

    /**
     * @param Bernard\Message $message
     */
    function it_denormalizes_envelope_and_delegates_message_to_marshaller($message, $marshaller)
    {
        $this->setMarshaller($marshaller);

        $marshaller->denormalize(array('key' => 'value'), 'Bernard\\Message', null)->willReturn($message);

        $normalized = array(
            'class'     => 'Bernard\\Message',
            'timestamp' => 1337,
            'message'   => array('key' => 'value'),
        );

        $envelope = $this->denormalize($normalized, 'Bernard\\Envelope');
        $envelope->shouldHaveType('Bernard\\Envelope');
        $envelope->getMessage()->shouldReturn($message);
        $envelope->getTimestamp()->shouldReturn(1337);
        $envelope->getClass()->shouldReturn('Bernard\\Message');
    }
}
