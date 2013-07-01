<?php

namespace Bernard\Pimple;

use Pimple;
use Bernard\Message\Envelope;

/**
 * @package Bernard
 */
class PimpleAwareResolver extends \Bernard\ServiceResolver\AbstractResolver
{
    protected $services = array();
    protected $pimple;

    /**
     * @param Pimple $pimple
     */
    public function __construct(Pimple $pimple)
    {
        $this->pimple = $pimple;
    }

    /**
     * {@inheritDoc}
     */
    public function register($name, $service)
    {
        $this->services[$name] = $service;
    }

    /**
     * {@inheritDoc}
     */
    protected function getService(Envelope $envelope)
    {
        $name = $envelope->getName();

        return isset($this->services[$name]) ? $this->pimple[$this->services[$name]] : null;
    }
}
