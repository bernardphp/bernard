<?php

namespace Bernard\Tests\Fixtures;

use Bernard\Envelope;
use Bernard\Queue;

class TickMiddleware implements \Bernard\Middleware
{
    public function __construct(&$result, $tick, $next = null)
    {
        $this->next = $next;
        $this->tick = $tick;
        $this->result = &$result;
    }

    public function call(Envelope $envelope, Queue $queue)
    {
        $this->tick();

        if (!$this->next) {
            return;
        }

        $this->next->call($envelope, $queue);

        $this->tick();
    }

    protected function tick()
    {
        $this->result .= $this->tick;
    }
}
