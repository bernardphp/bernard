<?php

namespace Bernard\Normalizer;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Bernard\Envelope;
use Normalt\Normalizer\AggregateNormalizer;
use Normalt\Normalizer\AggregateNormalizerAware;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class EnvelopeNormalizer implements NormalizerInterface, DenormalizerInterface, AggregateNormalizerAware
{
    private $aggregate;

    /**
     * @param AggregateNormalizer $aggregate
     */
    public function setAggregateNormalizer(AggregateNormalizer $aggregate)
    {
        $this->aggregate = $aggregate;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'class' => $object->getClass(),
            'timestamp' => $object->getTimestamp(),
            'message' => $this->aggregate->normalize($object->getMessage()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        try {
            Assertion::choicesNotEmpty($data, ['message', 'class', 'timestamp']);
            Assertion::classExists($data['class']);
        } catch (AssertionFailedException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        $envelope = new Envelope($this->aggregate->denormalize($data['message'], $data['class']));

        $this->forcePropertyValue($envelope, 'class', $data['class']);
        $this->forcePropertyValue($envelope, 'timestamp', $data['timestamp']);

        return $envelope;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === Envelope::class;
    }

    /**
     * {@inheritdoc}
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
        try {
            $property = new \ReflectionProperty($envelope, $property);
        } catch (\ReflectionException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        $property->setAccessible(true);
        $property->setValue($envelope, $value);
    }
}
