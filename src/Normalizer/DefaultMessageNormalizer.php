<?php

namespace Bernard\Normalizer;

use Assert\Assertion;
use Bernard\Message\DefaultMessage;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @package Bernard
 */
class DefaultMessageNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'name' => $object->getName(),
            'arguments' => $object->all(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        Assertion::choicesNotEmpty($data, ['name', 'arguments']);

        return new DefaultMessage($data['name'], $data['arguments']);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'Bernard\Message\DefaultMessage';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof DefaultMessage;
    }
}
