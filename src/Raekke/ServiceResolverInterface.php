<?php

namespace Raekke;

use Raekke\Message\MessageInterface;

/**
 * @package Raekke
 */
interface ServiceResolverInterface
{
    /**
     * @param string $name
     * @param mixed  $service
     */
    public function register($name, $service);

    /**
     * @param MessageInterface
     * @return object
     */
    public function resolve(MessageInterface $message);
}
