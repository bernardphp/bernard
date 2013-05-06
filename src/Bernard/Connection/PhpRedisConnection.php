<?php

namespace Bernard\Connection;

/**
 * Implements a Connection for use with https://github.com/nicolasff/phpredis
 *
 * @package Bernard
 */
class PhpRedisConnection implements \Bernard\Connection
{
    protected $redis;

    /**
     * @param Redis $client
     */
    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        return $this->redis->lLen('queue:' . $queueName);
    }

    /**
     * {@inheritDoc}
     */
    public function createQueue($queueName)
    {
        $this->redis->sAdd('queues', $queueName);
    }

    /**
     * {@inheritDoc}
     */
    public function removeQueue($queueName)
    {
        $this->redis->sRem('queues', $queueName);
        $this->redis->del($this->resolveKey($queueName));
    }

    /**
     * {@inheritDoc}
     */
    public function listQueues()
    {
        return $this->redis->sMembers('queues');
    }

    /**
     * {@inheritDoc}
     */
    public function pushMessage($queueName, $message)
    {
        $this->redis->rpush($this->resolveKey($queueName), $message);
    }

    /**
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        list(, $message) = $this->redis->blpop($this->resolveKey($queueName), $interval);

        return $message;
    }

    /**
     * {@inheritDoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        $limit += $index - 1;

        return $this->redis->lRange($this->resolveKey($queueName), $index, $limit);
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
        return $this->redis->info();
    }

    /**
     * Transform the queueName into a key.
     *
     * @param string $queueName
     * @return string
     */
    protected function resolveKey($queueName)
    {
        return 'queue:' . $queueName;
    }
}
