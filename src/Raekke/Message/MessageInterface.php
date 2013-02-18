<?php

namespace Raekke\Message;

/**
 * @package Raekke
 */
interface MessageInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getQueue();
}
