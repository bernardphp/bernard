<?php

namespace Bernard\Pimple;

use Pimple;

/**
 * @package Bernard
 */
class PimpleAwareRouter extends \Bernard\Router\SimpleRouter
{
    protected $pimple;

    /**
     * @param Pimple $pimple
     * @param array $receivers
     */
    public function __construct(Pimple $pimple, array $receivers = array())
    {
        parent::__construct($receivers);

        $this->pimple = $pimple;
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
