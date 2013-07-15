<?php

namespace Bernard;

use Bernard\Message\Envelope;
use Bernard\ServiceResolver\Invoker;

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
     * Resolves an envelope to an instance of a service object.
     *
     * @param  Envelope $envelope
     * @return object
     */
    public function resolve(Envelope $envelope);
}
