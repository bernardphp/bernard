<?php

namespace Bernard;

use Bernard\Message;

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
     * @return object
     */
    public function resolve(Message $message);
}
