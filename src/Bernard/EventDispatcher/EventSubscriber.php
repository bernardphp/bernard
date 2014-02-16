<?php

namespace Bernard\EventDispatcher;

use Bernard\EventDispatcher;

interface EventSubscriber
{
    /**
     * Use the $dispatcher to subscribe to events that
     * is emitted from Bernard
     *
     * @param EventDispatcher $dispatcher
     */
    public function subscribe(EventDispatcher $dispatcher);
}
