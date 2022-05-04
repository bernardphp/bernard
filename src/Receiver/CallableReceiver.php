<?php

declare(strict_types=1);

namespace Bernard\Receiver;

use Bernard\Message;
use Bernard\Receiver;

final class CallableReceiver implements Receiver
{
    /**
     * @var callable
     */
    private $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * {@inheritdoc}
     */
    public function receive(Message $message): void
    {
        \call_user_func($this->callable, $message);
    }
}
