<?php

namespace Bernard\EventDispatcher;

use Bernard\Message;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event related to a message.
 *
 * @package Bernard
 */
class MessageEvent extends Event
{
    private $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
