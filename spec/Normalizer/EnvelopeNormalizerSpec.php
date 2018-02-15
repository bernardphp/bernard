<?php

namespace spec\Bernard\Normalizer;

use Bernard\Envelope;
use Bernard\Message;
use Normalt\Normalizer\AggregateNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EnvelopeNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer_and_denormailzer()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_normalizes_envelope_and_delegates_message_to_aggregate(Envelope $envelope, Message $message, AggregateNormalizer $aggregate)
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

    function it_denormalizes_envelope_and_delegates_message_to_aggregate(Message $message, AggregateNormalizer $aggregate)
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
