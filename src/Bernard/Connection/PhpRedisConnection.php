<?php

namespace Bernard\Connection;

use Redis;

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
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * {@inheritDoc}
     */
    public function count($set)
    {
        return $this->redis->lLen($set);
    }

    /**s
     * {@inheritDoc}
     */
    public function all($set)
    {
        return $this->redis->sMembers($set);
    }

    /**
     * {@inheritDoc}
     */
    public function slice($set, $index = 0, $limit = 20)
    {
        return $this->redis->lRange($set, $index, $index + $limit - 1);
    }

    /**
     * {@inheritDoc}
     */
    public function get($set)
    {
        return $this->redis->get($set);
    }

    /**
     * {@inheritDoc}
     */
    public function pop($set, $interval = 5)
    {
        list(, $message) = $this->redis->blPop($set, $interval);

        return $message;
    }

    /**
     * {@inheritDoc}
     */
    public function push($set, $member)
    {
        $this->redis->rPush($set, $member);
    }

    /**
     * {@inheritDoc}
     */
    public function contains($set, $member)
    {
        return $this->redis->sContains($set, $member);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($set)
    {
        $this->redis->delete($set);
    }

    /**
     * {@inheritDoc}
     */
    public function insert($set, $member)
    {
        $this->redis->sAdd($set, $member);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($set, $member)
    {
        $this->redis->sRemove($set, $member);
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
        return $this->redis->info();
    }
}
