<?php

namespace Bernard\Router;

use Pimple;

/**
 * @package Bernard
 */
class PimpleAwareRouter extends SimpleRouter
{
    protected $pimple;

    /**
     * @param Pimple $pimple
     * @param array  $receivers
     */
    public function __construct(Pimple $pimple, array $receivers = [])
    {
        $this->pimple = $pimple;

        parent::__construct($receivers);
    }

    /**
     * {@inheritDoc}
     */
    protected function get($name)
    {
        return $this->pimple[parent::get($name)];
    }

    /**
     * {@inheritDoc}
     */
    protected function accepts($receiver)
    {
        return isset($this->pimple[$receiver]);
    }
}
