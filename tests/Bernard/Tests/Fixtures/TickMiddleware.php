<?php

namespace Bernard\Tests\Fixtures;

use Bernard\Envelope;

class TickMiddleware implements \Bernard\Middleware
{
    public function __construct(&$result, $tick, $next = null)
    {
        $this->next = $next;
        $this->tick = $tick;
        $this->result = &$result;
    }

    public function call(Envelope $envelope)
    {
        $this->tick();

        if (!$this->next) {
            return;
        }

        $this->next->call($envelope);

        $this->tick();
    }

    protected function tick()
    {
        $this->result .= $this->tick;
    }
}
