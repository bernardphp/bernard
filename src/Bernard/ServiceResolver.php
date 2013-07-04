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
     * @param  Envelope  $envelope
     * @return Invoker
     */
    public function resolve(Envelope $envelope);
}
