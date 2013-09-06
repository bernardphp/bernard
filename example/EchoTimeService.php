<?php

use Bernard\Message\DefaultMessage;

class EchoTimeService
{
    public function onEchoTime(DefaultMessage $message)
    {
        if (rand(0, 10) == 7) {
            throw new \RuntimeException('I failed because rand was 7');
        }

        usleep(100);
    }
}
