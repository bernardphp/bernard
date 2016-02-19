<?php

namespace Bernard\Driver;

/**
 * For some drivers it gives a performance boost not to query the backend
 * all the time. This is mostly for SQS and IronMQ.
 *
 * @package Bernard
 */
abstract class AbstractPrefetchDriver extends AbstractDriver
{
    protected $prefetch;
    protected $cache;

    /**
     * @param int|null $prefetch
     */
    public function __construct($prefetch = null)
    {
        $this->prefetch = $prefetch ? (integer) $prefetch : 2;
        $this->cache = new PrefetchMessageCache();
    }
}
