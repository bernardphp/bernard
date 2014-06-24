<?php

namespace Bernard\Batch;

use Bernard\Batch;
use Redis;

/**
 * Storage specific to php redis extension.
 *
 * @package Bernard
 */
class RedisStorage extends AbstractStorage
{
    protected $redis;
    protected $ttl;

    /**
     * @param Redis $redis
     * @param integer $ttl
     */
    public function __construct(Redis $redis, $ttl = null)
    {
        $this->redis = $redis;
        $this->ttl = $ttl ?: 3600 * 36;
    }

    /**
     * {@inheritDoc}
     */
    public function find($name)
    {
        $result = $this->redis->hmget($this->resolveKey($name), array('total', 'failed', 'successful', 'description'));

        return new Batch($name, $result['description'], new Status($result['total'], $result['failed'], $result['successful']));
    }

    /**
     * {@inheritDoc}
     */
    public function register($name)
    {
        $redis = $this->redis->multi();

        // insert the batch name into the set if dosent exists all ready.
        // we use sorted sets to cheat into not really expiring them.
        // we update this on last insert into the batch, to make sure we
        // dont add message and expire the batch instantanious.
        $redis->zadd('batches', $this->ttl + time(), $name);
        $redis->incr($this->resolveKey($name, 'total'));

        // create sets with expire at
        $redis->expire($this->resolveKey($name, 'total'), $this->ttl);
        $redis->expire($this->resolveKey($name, 'failed'), $this->ttl);
        $redis->expire($this->resolveKey($name, 'successful'), $this->ttl);

        // fire
        $redis->exec();
    }

    /**
     * {@inheritDoc}
     */
    public function increment($name, $type)
    {
        if (!in_array($type, array('failed', 'successful'))) {
            // should be more precise
            throw new \InvalidArgumentException();
        }

        // creates a new key if dosent exists, creates a new field with a
        // 0 as default if not exists.
        $this->redis->incr($this->resolveKey($name, $type));
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        $batches = $this->redis->zrangebyscore('batches', time(), '+inf');

        return array_map($batches, array($this, 'find'));
    }

    /**
     * {@inheritDoc}
     */
    protected function resolveKey($name, $type = null)
    {
        return rtrim('batch:' . $name . ':' . $type, ':');
    }
}
