<?php

declare(strict_types=1);

namespace Bernard;

class Util
{
    /**
     * Guesses the name of the queue.
     *
     * @return string
     */
    public static function guessQueue(Message $message)
    {
        return trim(strtolower(preg_replace('/[A-Z]/', '-\\0', $message->getName())), '-');
    }
}
