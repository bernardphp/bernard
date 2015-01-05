<?php

use Bernard\Util;
use Bernard\Message;

function bernard_guess_queue(Message $message)
{
    return Util::guessQueue($message);
}
