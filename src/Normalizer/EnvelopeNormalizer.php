<?php

namespace Bernard\Normalizer;

use Assert\Assertion;
use Bernard\Envelope;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @package Bernard
 */
class EnvelopeNormalizer extends AbstractAggregateNormalizerAware implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'class'     => $object->getClass(),
            'timestamp' => $object->getTimestamp(),
            'message'   => $this->aggregate->normalize($object->getMessage()),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        Assertion::choicesNotEmpty($data, array('message', 'class', 'timestamp'));

        Assertion::classExists($data['class']);

        $envelope = new Envelope($this->aggregate->denormalize($data['message'], $data['class']));

        $this->forcePropertyValue($envelope, 'class', $data['class']);
        $this->forcePropertyValue($envelope, 'timestamp', $data['timestamp']);

        return $envelope;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'Bernard\Envelope';
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Envelope;
    }

    /**
     * @param Envelope $envelope
     * @param string   $property
     * @param mixed    $value
     */
    private function forcePropertyValue(Envelope $envelope, $property, $value)
    {
        $property = new \ReflectionProperty($envelope, $property);
        $property->setAccessible(true);
        $property->setValue($envelope, $value);
    }
}
