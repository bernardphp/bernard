<?php

namespace Bernard\ServiceResolver;

use Bernard\Message;

/**
 * @package Bernard
 */
class ObjectResolver implements \Bernard\ServiceResolver
{
    protected $services = array();

    /**
     * {@inheritDoc}
     */
    public function register($name, $service)
    {
        if (!is_object($service)) {
            throw new \InvalidArgumentException('The given service is not an object.');
        }

        $this->services[$name] = $service;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Message $message)
    {
        if (isset($this->services[$message->getName()])) {
            return $this->services[$message->getName()];
        }

        throw new \InvalidArgumentException('No service registered for message "' . $message->getName() . '".');
    }
}
