<?php

namespace Bernard\Driver;

/**
 * Implements a Driver for use with https://github.com/nicolasff/phpredis
 *
 * @package Bernard
 */
class PhpRedisDriver implements \Bernard\Driver
{
    protected $redis;

    /**
     * @param Redis $redis
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
        // When PhpRedis is set up with an Redis::OPT_PREFIX
        // it does set the prefix to the key and to the timeout value something like:
        // "BLPOP" "bernard:queue:my-queue" "bernard:5"
        //
        // To set the resolved key in an array seems fixing this issue. We get:
        // "BLPOP" "bernard:queue:my-queue" "5"
        //
        // see https://github.com/nicolasff/phpredis/issues/158
        list(, $message) = $this->redis->blpop(array($this->resolveKey($queueName)), $interval) ?: null;

        return array($message, null);
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
    public function acknowledgeMessage($queueName, $receipt)
    {
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
     * @param  string $queueName
     * @return string
     */
    protected function resolveKey($queueName)
    {
        return 'queue:' . $queueName;
    }
}
