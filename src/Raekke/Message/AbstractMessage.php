<?php

namespace Raekke\Message;

/**
 * @package Raekke
 */
abstract class AbstractMessage
{
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        foreach ($parameters as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * @return string
     */
    public function getMessageName()
    {
        return end(explode('\\', get_class()));
    }
}
