<?php

namespace Bernard\EventDispatcher;

use Bernard\Message;

/**
 * Event for exceptions
 *
 * @package Bernard
 */
class ExceptionEvent extends MessageEvent
{
    protected $exception;

    /**
     * @param  Message   $messsage
     * @param  Exception $exception
     */
    public function __construct(Message $message, \Exception $exception)
    {
        parent::__construct($message);

        $this->exception = $exception;
    }

    /**
     * return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
