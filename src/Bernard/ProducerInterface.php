<?php

namespace Bernard;

use Bernard\Message;

/**
 * Responsible for distributing a message to the correct queue.
 *
 * @package Bernard
 */
interface ProducerInterface
{
    /**
     * @param Message $message
     */
    public function produce(Message $message);
}
