<?php

namespace Bernard\Batch;

use Bernard\Batch;

abstract class AbstractStorage implements Storage
{
    /**
     * {@inheritDoc}
     */
    public function reload(Batch $batch)
    {
        return $this->find($batch->getName());
    }
}
