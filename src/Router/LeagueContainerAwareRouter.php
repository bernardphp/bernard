<?php

namespace Bernard\Router;

use Bernard\Envelope;
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
     * {@inheritDoc}
     */
    protected function get($name)
    {
        $serviceId = parent::get($name);

        return $this->container->get($serviceId);
    }

    /**
     * {@inheritDoc}
     */
    protected function accepts($receiver)
    {
        return $this->container->isRegistered($receiver);
    }
}
