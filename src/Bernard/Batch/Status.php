<?php

namespace Bernard\Batch;

/**
 * ValueObject that holds different information about the current
 * status of a Batch
 *
 * @package Bernard
 */
final class Status
{
    private $total;
    private $failed;
    private $successful;

    public function __construct($total, $failed, $successful)
    {
        $this->total      = $total;
        $this->failed     = $failed;
        $this->successful = $successful;
    }

    public function isComplete()
    {
        return $this->total == $this->failed + $this->successful;
    }

    public function isRunning()
    {
        return false == $this->isComplete();
    }

    public function __get($property)
    {
        return $this->$property;
    }
}
