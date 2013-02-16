<?php

namespace Raekke\Message;

/**
 * @package Raekke
 */
class Message extends AbstractMessage
{
    protected $messageName;

    /**
     * @param string $messageName
     * @param array $parameters
     */
    public function __construct($messageName, array $parameters = array())
    {
        $this->messageName = $messageName;

        parent::__construct($parameters);
    }

    /**
     * @return string
     */
    public function getMessageName()
    {
        return $this->messageName;
    }
}
