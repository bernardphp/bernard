<?php

use Bernard\Message\PlainMessage;

class EchoTimeService
{
    public function echoTime(PlainMessage $message)
    {
        if (rand(0, 10) == 7) {
            throw new \RuntimeException('I failed because rand was 7');
        }

        usleep(100);
    }
}
