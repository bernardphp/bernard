<?php

namespace Bernard\Driver;

use Redis;

/**
 * Implements a Driver for use with https://github.com/nicolasff/phpredis
 *
 * @package Bernard
 */
class PhpRedisDriver implements \Bernard\Driver
{
    const QUEUE_PREFIX = 'queue:';
    
    protected $redis;

    /**
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * {@inheritdoc}
     */
    public function listQueues()
    {
        return $this->redis->sMembers('queues');
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue($queueName)
    {
        $this->redis->sAdd('queues', $queueName);
    }

    /**
     * {@inheritdoc}
     */
    public function countMessages($queueName)
    {
        return $this->redis->lLen(self::QUEUE_PREFIX . $queueName);
    }

    /**
     * {@inheritdoc}
     */
    public function pushMessage($queueName, $message)
    {
        $this->redis->rpush($this->resolveKey($queueName), $message);
    }

    /**
     * {@inheritdoc}
     */
    public function popMessage($queueName, $duration = 5)
    {
        // When PhpRedis is set up with an Redis::OPT_PREFIX
        // it does set the prefix to the key and to the timeout value something like:
        // "BLPOP" "bernard:queue:my-queue" "bernard:5"
        //
        // To set the resolved key in an array seems fixing this issue. We get:
        // "BLPOP" "bernard:queue:my-queue" "5"
        //
        // see https://github.com/nicolasff/phpredis/issues/158
        list(, $message) = $this->redis->blpop([$this->resolveKey($queueName)], $duration) ?: null;

        return [$message, null];
    }

    /**
     * {@inheritdoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        $limit += $index - 1;

        return $this->redis->lRange($this->resolveKey($queueName), $index, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function removeQueue($queueName)
    {
        $this->redis->sRem('queues', $queueName);
        $this->redis->del($this->resolveKey($queueName));
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        return $this->redis->info();
    }

    /**
     * Transform the queueName into a key.
     *
     * @param string $queueName
     *
     * @return string
     */
    protected function resolveKey($queueName)
    {
        return self::QUEUE_PREFIX . $queueName;
    }
}
