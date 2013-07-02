<?php

namespace Bernard\Driver;

/**
 * @package Bernard
 */
class PrefetchMessageCache
{
    protected $caches = array();

    /**
     * Pushes a $message to the end of the cache.
     *
     * @param string $queueName
     * @param array $message
     */
    public function push($queueName, array $message)
    {
        $cache = $this->get($queueName);
        $cache->enqueue($message);
    }

    /**
     * Get the next message in line. Or nothing if there is no more 
     * in the cache.
     *
     * @param string $queueName
     * @return array|null
     */
    public function pop($queueName)
    {
        $cache = $this->get($queueName);

        if ($this->contains($queueName)) {
            return $cache->dequeue();
        }
    }

    /**
     * Does the cache for a specific queue contain any more caches.
     *
     * @param string $queueName
     * @return boolean
     */
    public function contains($queueName)
    {
        return !$this->get($queueName)->isEmpty();
    }

    /**
     * Create the queue cache internally if it doesnt yes exists.
     *
     * @param string $queueName
     */
    protected function get($queueName)
    {
        if (isset($this->caches[$queueName])) {
            return $this->caches[$queueName];
        }

        return $this->caches[$queueName] = new \SplQueue;
    }
}
