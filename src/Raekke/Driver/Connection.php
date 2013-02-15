<?php

namespace Raekke\Driver;

use Redis;

/**
 * ext-redis wrapper.
 *
 * @package Raekke
 */
class Connection
{
    protected $redis;
    protected $configuration;

    /**
     * @param string $host
     * @param Configuration $configuration
     */
    public function __construct($host, Configuration $configuration = null)
    {
        $this->configuration = $configuration ?: new Configuration;

        $this->redis = new Redis;
        $this->redis->connect($host, 6379);

        $this->redis->setOption(Redis::OPT_PREFIX, $this->configuration->getNamespace());
    }

    public function add($key, $member)
    {
        $arguments = array_slice(func_get_args(), 1);

        array_unshift($arguments, $key);

        return call_user_func_array(array($this->redis, 'sAdd'), $arguments);
    }

    /**
     * Removes one or more elements from a set.
     * Supports *$members.
     * 
     * @param string $key
     * @param string $member
     * @return integer
     */
    public function remove($key, $member)
    {
        $arguments = array_slice(func_get_args(), 1);

        array_unshift($arguments, $key);

        return call_user_func_array(array($this->redis, 'sRemove'), $arguments);
    }

    /**
     * @param string $key
     * @param mixed $member
     */
    public function has($key, $member)
    {
        return (boolean) $this->redis->sContains($key, $member);
    }

    /**
     * @param string $key
     * @return array
     */
    public function all($key)
    {
        return $this->redis->sGetMembers($key);
    }

    /**
     * @param string $key
     * @return integer
     */
    public function count($key)
    {
        return $this->redis->sSize($key);
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return Redis
     */
    public function getRedis()
    {
        return $this->redis;
    }
}
