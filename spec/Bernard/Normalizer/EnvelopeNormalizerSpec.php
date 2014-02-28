<?php

namespace spec\Bernard\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EnvelopeNormalizerSpec extends ObjectBehavior
{
    /**
     * @param Symfony\Component\Serializer\Normalizer\NormalizerInterface $normalizer
     */
    function let($normalizer)
    {
        $normalizer->implement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
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
    function it_normalizes_envelope_and_delegates_message_to_normalizer($envelope, $message, $normalizer)
    {
        $envelope->getMessage()->willReturn($message);
        $envelope->getClass()->willReturn('Bernard\Message');
        $envelope->getTimestamp()->willReturn(1337);

        $normalizer->normalize($message)->willReturn(array(
            'key' => 'value',
        ));

        $this->setNormalizer($normalizer);

        $this->normalize($envelope)->shouldReturn(array(
            'class'     => 'Bernard\\Message',
            'timestamp' => 1337,
            'message'   => array('key' => 'value'),
        ));
    }

    /**
     * @param Bernard\Message $message
     */
    function it_denormalizes_envelope_and_delegates_message_to_normalizer($message, $normalizer)
    {
        $this->setNormalizer($normalizer);

        $normalizer->denormalize(array('key' => 'value'), 'Bernard\\Message', null)->willReturn($message);

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
