<?php

namespace Bernard\Tests\Fixtures;

use Bernard\Message\Envelope;

class EchoMiddleware implements \Bernard\Middleware
{
    public function __construct(&$result, $next = null)
    {
        $this->next = $next;
        $this->result = &$result;
    }

    public function call(Envelope $envelope)
    {
        if (!$this->next) {
            $this->result .= 'calling';

            return;
        }

        $this->result .= 'before';

        $this->next->call($envelope);

        $this->result .= 'after';
    }
}
