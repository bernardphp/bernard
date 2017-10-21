<?php

namespace Bernard;

/**
 * @package Bernard
 */
interface SerializerInterface
{
    /**
     * @param Envelope $envelope
     *
     * @return string
     */
    public function serialize(Envelope $envelope);

    /**
     * @param string $contents
     *
     * @return Envelope
     */
    public function unserialize($contents);
}
