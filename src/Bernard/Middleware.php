<?php

namespace Bernard;

/**
 * Builder of middleware. Takes any number of Callables and handles
 * them as factories returning a new invokable.
 *
 * @package Bernard
 */
interface Middleware
{
    /**
     * Remember to call the next middleware.
     *
     * @param Envelope $envelope
     */
    public function call(Envelope $envelope);
}
