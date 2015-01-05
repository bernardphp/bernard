<?php

namespace spec\Bernard\Normalizer;

use Normalt\Normalizer\AggregateNormalizer;
use Bernard\Envelope;
use Bernard\Message;
use PhpSpec\ObjectBehavior;

class EnvelopeNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Normalizer\EnvelopeNormalizer');
    }

    function it_is_a_normalizer_and_denormailzer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_normalizes_envelope_and_delegates_message_to_aggregate(Envelope $envelope, Message $message, AggregateNormalizer $aggregate)
    {
        $envelope->getMessage()->willReturn($message);
        $envelope->getClass()->willReturn('Bernard\Message\DefaultMessage');
        $envelope->getTimestamp()->willReturn(1337);

        $aggregate->normalize($message)->willReturn(array(
            'key' => 'value',
        ));

        $this->setAggregateNormalizer($aggregate);

        $this->normalize($envelope)->shouldReturn(array(
            'class'     => 'Bernard\Message\DefaultMessage',
            'timestamp' => 1337,
            'message'   => array('key' => 'value'),
        ));
    }

    function it_denormalizes_envelope_and_delegates_message_to_aggregate(Message $message, AggregateNormalizer $aggregate)
    {
        $this->setAggregateNormalizer($aggregate);

        $aggregate->denormalize(array('key' => 'value'), 'Bernard\Message\DefaultMessage', null)->willReturn($message);

        $normalized = array(
            'class'     => 'Bernard\Message\DefaultMessage',
            'timestamp' => 1337,
            'message'   => array('key' => 'value'),
        );

        $envelope = $this->denormalize($normalized, 'Bernard\Envelope');
        $envelope->shouldImplement('Bernard\Envelope');
        $envelope->getMessage()->shouldReturn($message);
        $envelope->getTimestamp()->shouldReturn(1337);
        $envelope->getClass()->shouldReturn('Bernard\Message\DefaultMessage');
    }

    function it_supports_denormalization_of_an_envelope()
    {
        $this->supportsDenormalization(array(), 'Bernard\Envelope')->shouldReturn(true);
    }

    function it_supports_normalization_of_an_envelope(Envelope $envelope)
    {
        $this->supportsNormalization($envelope)->shouldReturn(true);
    }
}
