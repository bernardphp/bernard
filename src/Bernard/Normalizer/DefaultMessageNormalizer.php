<?php

namespace Bernard\Normalizer;

use Bernard\Message\DefaultMessage;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class DefaultMessageNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'name'      => $object->getName(),
            'arguments' => $object->all(),
        );
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return new DefaultMessage($data['name'], $data['arguments']);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'Bernard\Message\DefaultMessage';
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof DefaultMessage;
    }
}
