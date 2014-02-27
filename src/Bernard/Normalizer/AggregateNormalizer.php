<?php

namespace Bernard\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use UnexpectedValueException;

class AggregateNormalizer implements NormalizerInterface, DenormalizerInterface
{
    protected $normalizers;

    public function __construct($normalizers = array())
    {
        $this->normalizers = array_merge($this->getDefaultNormalizers(), $normalizers);
    }

    public function getDefaultNormalizers()
    {
        return array(
            new EnvelopeNormalizer,
            new DefaultMessageNormalizer,
        );
    }

    public function normalize($object, $format = null, array $context = array())
    {
        if (false == $normalizer = $this->getNormalizer($object, $format)) {
            throw new UnexpectedValueException(sprintf('Could not normalize object of type %s, no supporting normalizer found.', get_class($object)));
        }

        if ($normalizer instanceof AggregateAware) {
            $normalizer->setAggregate($this);
        }

        return $normalizer->normalize($object, $format, $context);
    }

    public function denormalize($data, $type, $format = null, array $context = array())
    {
        if (false == $normalizer = $this->getDenormalizer($data, $type, $format)) {
            throw new UnexpectedValueException(sprintf('Could not denormalize object of type %s, no supporting normalizer found.', $type));
        }

        if ($normalizer instanceof AggregateAware) {
            $normalizer->setAggregate($this);
        }

        return $normalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->getNormalizer($data, $format) ? true : false;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->getDenormalizer($data, $format) ? true : false;
    }

    private function getNormalizer($object, $format)
    {
        foreach ($this->normalizers as $normalizer) {
            if (false == $normalizer instanceof NormalizerInterface) {
                continue;
            }

            if ($normalizer->supportsNormalization($object, $format)) {
                return $normalizer;
            }
        }
    }

    private function getDenormalizer($data, $class, $format)
    {
        foreach ($this->normalizers as $normalizer) {
            if (false == $normalizer instanceof DenormalizerInterface) {
                continue;
            }

            if ($normalizer->supportsDenormalization($data, $class, $format)) {
                return $normalizer;
            }
        }
    }

}
