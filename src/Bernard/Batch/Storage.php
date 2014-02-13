<?php

namespace Bernard\Batch;

use Bernard\Batch;

/**
 * Handles storage support for Batch. Implement this interface
 * to use another storage backend.
 *
 * @package Bernard
 */
interface Storage
{
    /**
     * @param integer $id
     * @return Batch
     */
    public function find($id);

    /**
     * Returns a new batch based on the given instance indentifier.
     *
     * @param Batch $batch
     * @return Batch
     */
    public function reload(Batch $batch);

    /**
     * @return Batch[]
     */
    public function all();

    /**
     * Register a batch with the storage, this may
     * be called multiple times. As it is called by the
     * middleware everytime a batch have been added
     *
     * @param string $id
     */
    public function register($id);

    /**
     * Increments a counter for a specific batch.
     *
     * @param string $id
     * @param string $type
     */
    public function increment($id, $type);
}
