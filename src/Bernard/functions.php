<?php

use Bernard\Message;

function bernard_guess_queue(Message $message)
{
    return trim(strtolower(preg_replace('/[A-Z]/', '-\\0', $message->getName())), '-');
}
