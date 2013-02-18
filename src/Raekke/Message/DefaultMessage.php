<?php

namespace Raekke\Message;

/**
 * @package Raekke
 */
class DefaultMessage extends Message
{
    protected $messageName;

    /**
     * @param string $messageName
     * @param array $parameters
     */
    public function __construct($messageName, array $parameters = array())
    {
        foreach ($parameters as $k => $v) {
            $this->$k = $v;
        }

        $this->messageName = $messageName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->messageName;
    }
}
