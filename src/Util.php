<?php

namespace Bernard;

/**
 * @package Bernard
 */
class Util
{
    /**
     * Guesses the name of the queue.
     *
     * @param Message $message
     *
     * @return string
     */
    public static function guessQueue(Message $message)
    {
        return trim(strtolower(preg_replace('/[A-Z]/', '-\\0', $message->getName())), '-');
    }
}
