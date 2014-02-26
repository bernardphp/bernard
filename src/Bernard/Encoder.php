<?php

namespace Bernard;

use Bernard\Encoder\Normalizer;
use Bernard\Encoder\GenericNormalizer;

class Encoder
{
    protected $normalizer;

    public function __construct(Normalizer $normalizer = null)
    {
        $this->normalizer = $normalizer ?: new GenericNormalizer;
    }

    public function encode(Envelope $envelope)
    {
        return json_encode($this->normalizeEnvelope($envelope));
    }

    public function decode($contents)
    {
        $data = json_decode($contents, true);

        return $this->denormalizeEnvelope($data);
    }

    private function normalizeEnvelope(Envelope $envelope)
    {
        return array(
            'class'     => $envelope->getClass(),
            'timestamp' => $envelope->getTimestamp(),
            'message'   => $this->normalizer->normalize($envelope->getMessage()),
        );
    }

    private function denormalizeEnvelope(array $data)
    {
        $envelope = new Envelope($this->normalizer->denormalize($data['class'], $data['message']));

        $this->forcePropertyValue($envelope, 'class', $data['class']);
        $this->forcePropertyValue($envelope, 'timestamp', $data['timestamp']);

        return $envelope;
    }

    private function forcePropertyValue(Envelope $envelope, $property, $value)
    {
        $property = new \ReflectionProperty($envelope, $property);
        $property->setAccessible(true);
        $property->setValue($envelope, $value);
    }
}
