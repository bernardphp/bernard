<?php

namespace Bernard;

class EventDispatcher extends \Evenement\EventEmitter
{
    /**
     * Calls the register method on the subscriber providing a
     * low level extension system
     *
     * @param EventSubscriber $subscriber
     */
    public function subscribe(EventSubscriber $subscriber)
    {
        $subscriber->subscribe($this);
    }
}
