<?php

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
    public function receive(Message $message)
    {
        call_user_func($this->callable, $message);
    }
}
