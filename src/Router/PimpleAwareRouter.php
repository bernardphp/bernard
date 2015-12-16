<?php

namespace Bernard\Router;
use Pimple\Container;

/**
 * @package Bernard
 */
class PimpleAwareRouter extends SimpleRouter
{
    protected $pimple;

    /**
     * @param Container $pimple
     * @param array  $receivers
     */
    public function __construct(Container $pimple, array $receivers = [])
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
