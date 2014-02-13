<?php

namespace Bernard;

use Bernard\Batch\Storage;
use Bernard\Batch\Status;

final class Batch implements Message
{
    private $name;
    private $description;
    private $status;
    private $envelopes = array();

    public function __construct($name = null, $description = '', Status $status = null)
    {
        $this->name        = $name ?: uniqid('bernard_batch', true);
        $this->description = $description;
        $this->status      = $status ?: new Status(0, 0, 0);
    }

    public function assign(Message $message)
    {
        $this->envelopes[] = new Envelope($message, array('batch' => $this->name));

        $this->status = new Status($this->status->total + 1, $this->status->failed, $this->status->successful);
    }

    /**
     * Internal method used by the producer to produce the envelopes
     * through its mnamedlewares.
     *
     * @return array
     */
    public function flush()
    {
        $envelopes = $this->envelopes;
        $this->envelopes = array();

        return $envelopes;
    }

    public function isRunning()
    {
        return $this->status->isRunning();
    }

    public function isComplete()
    {
        return $this->status->isComplete();
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }
}
