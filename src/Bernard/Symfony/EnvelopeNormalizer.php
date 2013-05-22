<?php

namespace Bernard\Symfony;

use Bernard\Message\Envelope;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Normalizer/Denormalizer which supports Envelope and its embedded messages. It only
 * allows messages to contain simple values for it properties.
 *
 * @package Bernard
 */
class EnvelopeNormalizer extends SerializerAwareNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'args'      => $this->serializer->normalize($object->getMessage(), $format, $context),
            'class'     => str_replace('\\', ':', $object->getClass()),
            'timestamp' => $object->getTimestamp(),
            'retries'   => $object->getRetries(),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $data['class'] = $class = str_replace(':', '\\', $data['class']);

        if (!class_exists($class)) {
            $class = 'Bernard\\Message\\DefaultMessage';
            $data['args']['name'] = current(array_reverse(explode('\\', $data['class'])));
        }

        $envelope = new Envelope($this->serializer->denormalize($data['args'], $class, $format, $context));

        foreach (array('timestamp', 'retries', 'class') as $name) {
            $property = new \ReflectionProperty($envelope, $name);
            $property->setAccessible(true);
            $property->setValue($envelope, $data[$name]);
        }

        return $envelope;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->supports($data);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->supports($type);
    }

    /**
     * @param string|object $class
     * @return boolean
     */
    protected function supports($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return $class == 'Bernard\Message\Envelope';
    }
}
