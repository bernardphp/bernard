<?php

namespace Bernard\Symfony;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @package Bernard
 */
class ContainerAwareRouter extends \Bernard\Router\AbstractRouter
{
    public function __construct(ContainerInterface $container, array $receivers = array())
    {
        $this->container = $container;

        parent::__construct($receivers);
    }

    /**
     * {@inheritDoc}
     */
    protected function get($name)
    {
        return $this->container->get(parent::get($name));
    }

    /**
     * {@inheritDoc}
     */
    protected function accepts($receiver)
    {
        return $this->container->has($receiver);
    }
}
