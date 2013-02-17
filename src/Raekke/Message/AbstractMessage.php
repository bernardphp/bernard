<?php

namespace Raekke\Message;

/**
 * @package Raekke
 */
abstract class AbstractMessage
{
    protected $header;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        foreach ($parameters as $k => $v) {
            $this->$k = $v;
        }

        $this->header = new MessageHeader;
    }

    /**
     * @return MessageHeader
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return string
     */
    public function getMessageName()
    {
        return end(explode('\\', get_class()));
    }
}
