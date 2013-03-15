<?php

namespace Bernard;

use Bernard\Message\Envelope;

/**
 * @package Bernard
 */
interface Serializer
{
    /**
     * @param  Envelope $message
     * @return string
     */
    public function serialize(Envelope $message);

    /**
     * @return Envelope
     */
    public function deserialize($serialized);
}
