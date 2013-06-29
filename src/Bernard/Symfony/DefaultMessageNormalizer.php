<?php

namespace Bernard\Symfony;

use Bernard\Message\DefaultMessage;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @package Bernard
 */
class DefaultMessageNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return array('name' => $object->getName()) + get_object_vars($object);
    }

    /**
     * {@inheritDoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return new DefaultMessage($data['name'], $data);
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

        return $class == 'Bernard\Message\DefaultMessage';
    }
}
