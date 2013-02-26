<?php

use Raekke\Message\DefaultMessage;

class EchoTimeService
{
    public function onEchoTime(DefaultMessage $message)
    {
        echo "Time is: " . $message->time . "\n";

        sleep(1);
    }
}
