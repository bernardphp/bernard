<?php

namespace Bernard\Symfony;

use Bernard\Message\Envelope;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Normalizer/Denormalizer which supports Envelope and its embedded messages. It only
 * allows messages to contain simple values for it properties.
 *
 * @package Bernard
 */
class EnvelopeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $envelope = $object;

        $data = array(
            'args'      => new \stdClass,
            'class'     => str_replace('\\', ':', $envelope->getClass()),
            'timestamp' => $envelope->getTimestamp(),
            'retries'   => $envelope->getRetries(),
        );

        $message = $envelope->getMessage();
        $object = new \ReflectionObject($message);

        foreach ($object->getProperties() as $property) {
            $property->setAccessible(true);

            $data['args']->{$property->getName()} = $property->getValue($envelope->getMessage());
        }

        return $data;
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

        $message = $this->createObjectWithoutConstructor($class);
        $envelope = new Envelope($message);

        foreach ($data['args'] as $name => $value) {
            $this->setPropertyValue($message, $name, $value);
        }

        foreach (array('timestamp', 'retries', 'class') as $name) {
            $this->setPropertyValue($envelope, $name, $data[$name]);
        }

        return $envelope;
    }

    public function setPropertyValue($object, $property, $value)
    {
        if (!property_exists($object, $property)) {
            $object->$property = $value;

            return;
        }

        $property = new \ReflectionProperty($object, $property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $this->supports(get_class($data));
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->supports($type);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($class)
    {
        return $class == 'Bernard\Message\Envelope';
    }

    /**
     * @param  string $class
     * @return object
     */
    protected function createObjectWithoutConstructor($class)
    {
        return unserialize(sprintf('O:%u:"%s":0:{}', strlen($class), $class));
    }
}
