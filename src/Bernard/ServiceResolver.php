<?php

namespace Bernard;

use Bernard\Message\Envelope;

/**
 * @package Bernard
 */
interface ServiceResolver
{
    /**
     * @param string          $name
     * @param object|callable $service
     */
    public function register($name, $service);

    /**
     * Resolves an envelope to a callable.
     *
     * @param  Envelope $envelope
     * @return array
     */
    public function resolve(Envelope $envelope);
}
