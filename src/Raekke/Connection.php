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
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param  string  $set
     * @return integer
     */
    public function count($set)
    {
        return $this->client->llen($set);
    }

    /**
     * @param  string  $set
     * @return mixed[]
     */
    public function all($set)
    {
        return $this->client->smembers($set);
    }

    /**
     * @param  string  $set
     * @param  integer $index
     * @param  integer $limit
     * @return mixed[]
     */
    public function slice($set, $index = 0, $limit = 20)
    {
        return $this->client->lrange($set, $index, $index + $limit - 1);
    }

    /**
     * @param  string $set
     * @return mixed
     */
    public function get($set)
    {
        return $this->client->get($set);
    }

    /**
     * @param  string     $set
     * @param  integer    $interval
     * @return mixed|null
     */
    public function pop($set, $interval = 5)
    {
        list(, $message) = $this->client->blpop($set, $interval);

        return $message;
    }

    /**
     * @param string $set
     * @param mixed  $member
     */
    public function push($set, $member)
    {
        $this->client->rpush($set, $member);
    }

    /**
     * @param  string  $set
     * @param  mixed   $member
     * @return boolean
     */
    public function contains($set, $member)
    {
        return $this->client->sismember($set, $member);
    }

    /**
     * @param string $set
     */
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
     * @return array
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
