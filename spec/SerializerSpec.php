<?php

namespace spec\Bernard;

use Bernard\Envelope;
use Bernard\Message;
use Bernard\Message\PlainMessage;
use Normalt\Normalizer\AggregateNormalizer;

class SerializerSpec extends \PhpSpec\ObjectBehavior
{
    function let(AggregateNormalizer $aggregate)
    {
        $this->beConstructedWith($aggregate);
    }

    function it_serializes_normalized_envelope_into_json(Message $message, AggregateNormalizer $aggregate)
    {
        $envelope = new Envelope($message->getWrappedObject());
        $aggregate->normalize($envelope, null)->willReturn([
            'class' => PlainMessage::class,
            'timestamp' => 1337,
            'message' => ['name' => 'Import', 'arguments' => ['arg1' => 'value']],
        ]);

        $this->serialize($envelope)
            ->shouldReturn('{"class":"Bernard\\\\Message\\\\PlainMessage","timestamp":1337,"message":{"name":"Import","arguments":{"arg1":"value"}}}');
    }

    function it_unserializes_into_envelope(Message $message, AggregateNormalizer $aggregate)
    {
        $envelope = new Envelope($message->getWrappedObject());

        $normalized = [
            'class' => PlainMessage::class,
            'timestamp' => 1337,
            'message' => ['name' => 'Import', 'arguments' => ['arg1' => 'value']],
        ];

        $aggregate->denormalize($normalized, Envelope::class, null)->willReturn($envelope);

        $this->unserialize('{"class":"Bernard\\\\Message\\\\PlainMessage","timestamp":1337,"message":{"name":"Import","arguments":{"arg1":"value"}}}')
            ->shouldReturn($envelope);
    }
}
