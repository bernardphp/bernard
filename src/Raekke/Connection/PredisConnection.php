<?php

namespace Raekke\Connection;

use Predis\ClientInterface;

/**
 * @package Raekke
 */
class PredisConnection implements \Raekke\Connection
{
    protected $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function count($set)
    {
        return $this->client->llen($set);
    }

    /**
     * {@inheritDoc}
     */
    public function all($set)
    {
        return $this->client->smembers($set);
    }

    /**
     * {@inheritDoc}
     */
    public function slice($set, $index = 0, $limit = 20)
    {
        return $this->client->lrange($set, $index, $index + $limit - 1);
    }

    /**
     * {@inheritDoc}
     */
    public function get($set)
    {
        return $this->client->get($set);
    }

    /**
     * {@inheritDoc}
     */
    public function pop($set, $interval = 5)
    {
        list(, $message) = $this->client->blpop($set, $interval);

        return $message;
    }

    /**
     * {@inheritDoc}
     */
    public function push($set, $member)
    {
        $this->client->rpush($set, $member);
    }

    /**
     * {@inheritDoc}
     */
    public function contains($set, $member)
    {
        return $this->client->sismember($set, $member);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($set)
    {
        $this->client->del($set);
    }

    /**
     * {@inheritDoc}
     */
    public function insert($set, $member)
    {
        $this->client->sadd($set, $member);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($set, $member)
    {
        $this->client->srem($set, $member);
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
        // Temporarily change the command use to get info as earlier and newer redis
        // versions breaks it into sections.
        $commandClass = $this->client->getProfile()->getCommandClass('info');
        $this->client->getProfile()->defineCommand('info', 'Predis\Command\ServerInfo');

        $info = $this->client->info();

        $this->client->getProfile()->defineCommand('info', $commandClass);

        return $info;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }
}
