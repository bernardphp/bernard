<?php

namespace spec\Bernard\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EnvelopeNormalizerSpec extends ObjectBehavior
{
    /**
     * @param Normalt\Normalizer\AggregateNormalizer $aggregate
     */
    function let($aggregate)
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
    function it_normalizes_envelope_and_delegates_message_to_aggregate($envelope, $message, $aggregate)
    {
        $envelope->getMessage()->willReturn($message);
        $envelope->getClass()->willReturn('Bernard\Message\PlainMessage');
        $envelope->getTimestamp()->willReturn(1337);

        $aggregate->normalize($message)->willReturn(array(
            'key' => 'value',
        ));

        $this->setAggregateNormalizer($aggregate);

        $this->normalize($envelope)->shouldReturn(array(
            'class'     => 'Bernard\\Message\\PlainMessage',
            'timestamp' => 1337,
            'message'   => array('key' => 'value'),
        ));
    }

    /**
     * @param Bernard\Message $message
     */
    function it_denormalizes_envelope_and_delegates_message_to_aggregate($message, $aggregate)
    {
        $this->setAggregateNormalizer($aggregate);

        $aggregate->denormalize(array('key' => 'value'), 'Bernard\\Message\\PlainMessage', null)->willReturn($message);

        $normalized = array(
            'class'     => 'Bernard\\Message\\PlainMessage',
            'timestamp' => 1337,
            'message'   => array('key' => 'value'),
        );

        $envelope = $this->denormalize($normalized, 'Bernard\\Envelope');
        $envelope->shouldHaveType('Bernard\\Envelope');
        $envelope->getMessage()->shouldReturn($message);
        $envelope->getTimestamp()->shouldReturn(1337);
        $envelope->getClass()->shouldReturn('Bernard\\Message\\PlainMessage');
    }
}
