<?php

namespace Bernard\Symfony;

use Bernard\Envelope;
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
            'class'     => bernard_encode_class_name($object->getClass()),
            'timestamp' => $object->getTimestamp(),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $data['class'] = $class = bernard_decode_class_string($data['class']);

        if (!class_exists($class)) {
            $class = 'Bernard\\Message\\DefaultMessage';
            $data['args']['name'] = substr(strrchr($data['class'], '\\'), 1);
        }

        $envelope = new Envelope($this->serializer->denormalize($data['args'], $class, $format, $context));

        foreach (array('timestamp', 'class') as $name) {
            bernard_force_property_value($envelope, $name, $data[$name]);
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
     * @param  string|object $class
     * @return boolean
     */
    protected function supports($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return $class == 'Bernard\Envelope';
    }
}
