<?php

namespace Bernard\Router;

use Interop\Container\ContainerInterface;

class ContainerInteropAwareRouter extends SimpleRouter
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     * @param array              $receivers
     */
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
