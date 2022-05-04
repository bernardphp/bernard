<?php

declare(strict_types=1);

namespace Bernard\Normalizer;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Bernard\Message\PlainMessage;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PlainMessageNormalizer implements NormalizerInterface, DenormalizerInterface
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
        try {
            Assertion::notEmptyKey($data, 'name');
            Assertion::keyExists($data, 'arguments');
            Assertion::isArray($data['arguments']);
        } catch (AssertionFailedException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        return new PlainMessage($data['name'], $data['arguments']);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === PlainMessage::class;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PlainMessage;
    }
}
