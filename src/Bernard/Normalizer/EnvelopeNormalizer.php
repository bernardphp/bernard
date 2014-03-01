<?php

namespace Bernard\Normalizer;

use Bernard\Envelope;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EnvelopeNormalizer extends AbstractMarshallerAware implements NormalizerInterface, DenormalizerInterface
{
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'class'     => $object->getClass(),
            'timestamp' => $object->getTimestamp(),
            'message'   => $this->marshaller->normalize($object->getMessage()),
        );
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $envelope = new Envelope($this->marshaller->denormalize($data['message'], $data['class']));

        $this->forcePropertyValue($envelope, 'class', $data['class']);
        $this->forcePropertyValue($envelope, 'timestamp', $data['timestamp']);

        return $envelope;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'Bernard\Envelope';
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Envelope;
    }

    private function forcePropertyValue(Envelope $envelope, $property, $value)
    {
        $property = new \ReflectionProperty($envelope, $property);
        $property->setAccessible(true);
        $property->setValue($envelope, $value);
    }
}
