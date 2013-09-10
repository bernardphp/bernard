<?php

namespace Bernard;

use Bernard\Message\Envelope;

/**
 * Builder of middleware. Takes any number of Callables and handles
 * them as factories returning a new invokable.
 *
 * @package Raven
 */
interface Middleware
{
    /**
     * Rememeber to call the next middleware.
     *
     * @param Envelope $envelope
     */
    public function call(Envelope $envelope);
}
