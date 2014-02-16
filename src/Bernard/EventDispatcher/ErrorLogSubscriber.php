<?php

namespace Bernard\EventDispatcher;

use Bernard\EventDispatcher;
use Bernard\Envelope;
use Bernard\Queue;

class ErrorLogSubscriber implements EventSubscriber
{
    public function onReject(Envelope $envelope, Queue $queue, \Exception $e)
    {
        error_log(sprintf('[bernard] caught exception %s::%s while processing %s.', 
           get_class($e), $e->getMessage(), $envelope->getName()));
    }

    public function subscribe(EventDispatcher $dispatcher)
    {
        $dispatcher->on('bernard.reject', array($this, 'onReject'));
    }
}
