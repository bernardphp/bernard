<?php

namespace Bernard\Router;

/**
 * Routes a Envelope to a Receiver by using the name of the Envelope.
 *
 * @package Bernard
 */
class SimpleRouter extends AbstractRouter
{
    /**
     * @param  mixed   $receiver
     * @return boolean
     */
    protected function accepts($receiver)
    {
        return is_callable($receiver) || is_object($receiver) || class_exists($receiver);
    }

    /**
     * @param  string $name
     * @return mixed
     */
    protected function get($name)
    {
        return $this->receivers[$name];
    }
}
