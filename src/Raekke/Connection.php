<?php

namespace Raekke;

use Predis\ClientInterface;

/**
 * @package Raekke
 */
class Connection
{
    protected $client;

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

    public function get($set)
    {
        return $this->client->get($set);
    }

    public function pop($set, $interval = 5)
    {
        list(, $message) = $this->client->blpop($set, $interval);

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

    public function insert($set, $member)
    {
        $this->client->sadd($set, $member);
    }

    public function remove($set, $member)
    {
        $this->client->srem($set, $member);
    }

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

    public function getClient()
    {
        return $this->client;
    }
}
