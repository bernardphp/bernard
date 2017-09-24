<?php

namespace Bernard\Router;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @package Bernard
 */
class SymfonyContainerAwareRouter extends SimpleRouter
{
    private $container;

    /**
     * @param ContainerInterface $container
     * @param array              $receivers
     */
    public function __construct(ContainerInterface $container, array $receivers = [])
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
