<?php

namespace Bernard\Spork\Exception;

/**
 * Special Exception that is created based on another exception which happend
 * while executing code in a fork.
 *
 * @package Bernard
 */
class ProcessException extends \LogicException
{
    protected $class;

    /**
     * @param string  $class
     * @param string  $message
     * @param string  $file
     * @param integer $line
     * @param integer $code
     */
    public function __construct($class, $message = '', $file = '', $line = 0, $code = 0)
    {
        parent::__construct($message, $code);

        $this->file = $file;
        $this->line = $line;
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }
}
