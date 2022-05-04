<?php

declare(strict_types=1);

namespace Bernard\Driver;

/**
 * Message is returned by a Driver containing the raw message and optionally a receipt for acknowledging the message.
 */
final class Message
{
    public function __construct(
        public string $message,
        public mixed $receipt = null,
    ) {
    }
}
