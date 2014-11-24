<?php

namespace Bernard\Driver;

use Bernard\Driver;

/**
 * For some drivers it gives a performance boost not to query the backend
 * all the time. This is mostly for SQS and IronMQ.
 *
 * @package Bernard
 */
abstract class AbstractPrefetchDriver implements Driver
{
    /**
     * @var integer
     */
    protected $prefetch;

    /**
     * @var PrefetchMessageCache
     */
    protected $cache;

    /**
     * @param integer|null $prefetch
     */
    public function __construct($prefetch = null)
    {
        $this->prefetch = $prefetch ? (integer) $prefetch : 2;
        $this->cache = new PrefetchMessageCache;
    }
}
