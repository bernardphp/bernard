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
        $this->messageName = $messageName;

        parent::__construct($parameters);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->messageName;
    }

    public function getMessageName()
    {
        // TODO: implement
    }
}
