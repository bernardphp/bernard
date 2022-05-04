<?php

declare(strict_types=1);

namespace Bernard\Driver;

/**
 * For some drivers it gives a performance boost not to query the backend
 * all the time. This is mostly for SQS and IronMQ.
 */
abstract class AbstractPrefetchDriver implements \Bernard\Driver
{
    protected ?int $prefetch;

    protected PrefetchMessageCache $cache;

    public function __construct(?int $prefetch = null)
    {
        $this->prefetch = $prefetch ?? 2;
        $this->cache = new PrefetchMessageCache();
    }
}
