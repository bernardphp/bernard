<?php

namespace Bernard\Router;

use League\Container\ContainerInterface;

/**
 * @package Bernard
 */
class LeagueContainerAwareRouter extends SimpleRouter
{
    private $container;

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
     * {@inheritdoc}
     */
    protected function get($name)
    {
        $serviceId = parent::get($name);

        return $this->container->get($serviceId);
    }

    /**
     * {@inheritdoc}
     */
    protected function accepts($receiver)
    {
        return $this->container->has($receiver);
    }
}
