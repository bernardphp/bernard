<?php

namespace Raekke\ServiceResolver;

use Raekke\Message\MessageInterface;

/**
 * @package Raekke
 */
interface ServiceResolverInterface
{
    /**
     * @param string          $name
     * @param object|callable $service
     */
    public function register($name, $service);

    /**
     * @param MessageInterface
     * @return object
     */
    public function resolve(MessageInterface $message);
}
