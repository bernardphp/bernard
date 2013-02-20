<?php

namespace Raekke;

use Predis\ClientInterface;

/**
 * @package Raekke
 */
class Connection
{
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function count($set)
    {
        return $this->client->llen($set);
    }

    public function all($set)
    {
        return $this->client->smembers($set);
    }

    public function slice($set, $index = 0, $limit = 20)
    {
        return $this->client->lrange($set, $index, $index + $limit - 1);
    }

    public function pop($set, $interval = 5)
    {
        list($set, $message) = $this->client->blpop($set, $interval);

        return $message;
    }

    public function push($set, $member)
    {
        $this->client->rpush($set, $member);
    }

    public function has($set, $member)
    {
        return $this->client->sismember($set, $member);
    }

    public function delete($set)
    {
        $this->client->del($set);
    }

    /**
     * @param string $set
     * @param mixed  $member
     */
    public function insert($set, $member)
    {
        $this->client->sadd($set, $member);
    }

    /**
     * @param string $set
     * @param mixed  $member
     */
    public function remove($set, $member)
    {
        $this->client->srem($set, $member);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
