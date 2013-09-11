<?php

namespace Bernard\Pimple;

use Pimple;
use Bernard\Envelope;

/**
 * @package Bernard
 */
class PimpleAwareResolver extends \Bernard\ServiceResolver\AbstractResolver
{
    protected $pimple;

    /**
     * @param Pimple $pimple
     * @param array  $services
     */
    public function __construct(Pimple $pimple, array $services = array())
    {
        $this->pimple = $pimple;

        parent::__construct($services);
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
