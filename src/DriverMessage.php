<?php

declare(strict_types=1);

namespace Bernard;

/**
 * DriverMessage is returned by a Driver containing the raw message and optionally a receipt for acknowledging the message.
 */
final class DriverMessage
{
    /**
     * @readonly
     */
    public string $message;

    /**
     * @readonly
     */
    public mixed $receipt;

    public function __construct(string $message, mixed $receipt = null)
    {
        $this->message = $message;
        $this->receipt = $receipt;
    }
}
