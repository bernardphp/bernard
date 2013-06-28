<?php

namespace Bernard;

use Bernard\Message\Envelope;
use Bernard\ServiceResolver\Invocator;

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
     * @param  Envelope   $envelope
     * @return Invocator
     */
    public function resolve(Envelope $envelope);
}
