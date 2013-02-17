<?php

namespace Raekke\Message;

/**
 * @package Raekke
 */
interface MessageInterface
{
    /**
     * @return MessageHeader
     */
    public function getHeader();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getQueue();
}
