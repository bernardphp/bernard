<?php

namespace Bernard\Serializer;

use Bernard\Envelope;
use Symfony\Component\Serializer\Serializer;

/**
 * @package Bernard
 */
class SymfonySerializer implements \Bernard\Serializer
{
    protected $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize(Envelope $envelope)
    {
        return $this->serializer->serialize($envelope, 'json');
    }

    /**
     * {@inheritDoc}
     */
    public function deserialize($serialized)
    {
        return $this->serializer->deserialize($serialized, 'Bernard\Envelope', 'json');
    }
}
