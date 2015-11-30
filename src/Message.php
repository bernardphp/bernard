<?php

namespace Bernard;

/**
 * @package Bernard
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
