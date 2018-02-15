<?php

namespace spec\Bernard\Normalizer;

use Bernard\Envelope;
use Bernard\Message;
use Normalt\Normalizer\AggregateNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EnvelopeNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer_and_denormailzer()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldImplement(DenormalizerInterface::class);
    }

    function it_normalizes_envelope_and_delegates_message_to_aggregate(Envelope $envelope, Message $message, AggregateNormalizer $aggregate)
    {
        $envelope->getMessage()->willReturn($message);
        $envelope->getClass()->willReturn(Message\PlainMessage::class);
        $envelope->getTimestamp()->willReturn(1337);

        $aggregate->normalize($message)->willReturn([
            'key' => 'value',
        ]);

        $this->setAggregateNormalizer($aggregate);

        $this->normalize($envelope)->shouldReturn([
            'class' => Message\PlainMessage::class,
            'timestamp' => 1337,
            'message' => ['key' => 'value'],
        ]);
    }

    function it_denormalizes_envelope_and_delegates_message_to_aggregate(Message $message, AggregateNormalizer $aggregate)
    {
        $this->setAggregateNormalizer($aggregate);

        $aggregate->denormalize(['key' => 'value'], Message\PlainMessage::class, null)->willReturn($message);

        $normalized = [
            'class' => Message\PlainMessage::class,
            'timestamp' => 1337,
            'message' => ['key' => 'value'],
        ];

        $envelope = $this->denormalize($normalized, Envelope::class);
        $envelope->shouldHaveType(Envelope::class);
        $envelope->getMessage()->shouldReturn($message);
        $envelope->getTimestamp()->shouldReturn(1337);
        $envelope->getClass()->shouldReturn(Message\PlainMessage::class);
    }
}
