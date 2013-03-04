<?php

namespace Raekke;

/**
 * @package Raekke
 */
interface Message
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
