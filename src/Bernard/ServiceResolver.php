<?php

namespace Bernard;

use Bernard\Message;
use Bernard\ServiceResolver\Invocator;

/**
 * @package Bernard
 */
interface ServiceResolver
{
    /**
     * @param string          $name
     * @param object|callable $service
     */
    public function register($name, $service);

    /**
     * @param Message $message
     * @return Invocator
     */
    public function resolve(Message $message);
}
