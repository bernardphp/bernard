<?php

namespace Bernard\Router;

use Bernard\Envelope;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * ContainerReceiverResolver resolves a receiver from a container.
 */
final class ContainerReceiverResolver extends SimpleReceiverResolver
{
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function accepts($receiver)
    {
        return $this->container->has($receiver);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($receiver, Envelope $envelope)
    {
        try {
            $receiver = $this->container->get($receiver);
        } catch (NotFoundExceptionInterface $e) {
            return null;
        }

        return parent::resolve($receiver, $envelope);
    }
}
