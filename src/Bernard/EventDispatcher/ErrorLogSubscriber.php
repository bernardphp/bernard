<?php

namespace Bernard\EventDispatcher;

use Bernard\EventDispatcher;
use Bernard\Envelope;
use Bernard\Queue;

class ErrorLogSubscriber implements EventSubscriber
{
    public function onException(Envelope $envelope, Queue $queue, \Exception $e)
    {
        error_log(sprintf('[bernard] caught exception %s::%s while processing %s.', 
           get_class($e), $e->getMessage(), $envelope->getName()));
    }

    public function subscribe(EventDispatcher $dispatcher)
    {
        $dispatcher->on('bernard.exception', array($this, 'onException'));
    }
}
