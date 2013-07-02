<?php

namespace Bernard\Driver;

use SplQueue;

/**
 * For some drivers it gives a performance boost not to query the backend
 * all the time. This is mostly for SQS and IronMQ.
 *
 * @package Bernard
 */
abstract class AbstractPrefetchDriver implements \Bernard\Driver
{
    protected $perfetch;
    protected $caches;

    /**
     * @param integer|null $prefetch
     */
    public function __construct($prefetch = null)
    {
        $this->prefetch = $prefetch ? (integer) $prefetch : 2;
    }

    /**
     * Returns null if there is no cached messages
     *
     * @param string $queueName
     * @return array|null
     */
    protected function cached($queueName)
    {
        if (!isset($this->caches[$queueName])) {
            $this->caches[$queueName] = new SplQueue;
        }

        $cache = $this->caches[$queueName];

        if (!$cache->isEmpty()) {
            return $cache->dequeue();
        }
    }

    /**
     * @param string $queueName
     * @param array $message A message array in the form of array($body, $receipt)
     */
    protected function cache($queueName, array $message)
    {
        $this->caches[$queueName]->enqueue($message);
    }
}
