<?php

namespace Raekke\Driver;

use Predis\Client;
use Predis\Command\Processor\KeyPrefixProcessor;

/**
 * @package Raekke
 */
class Connection
{
    protected $client;
    protected $configuration;

    /**
     * @param string|array $server
     * @param Configuration $configuration
     */
    public function __construct($server, Configuration $configuration)
    {
        $this->client = new Client($server, array('prefix' => $configuration->getPrefix()));
        $this->configuration = $configuration;
    }

    public function count($set)
    {
        return $this->client->llen($set);
    }

    public function all($set)
    {
        return $this->client->smembers($set);
    }

    public function push($set, $member)
    {
        $this->client->rpush($set, $member);
    }

    /**
     * @param string $set
     * @param mixed $member
     */
    public function insert($set, $member)
    {
        $this->client->sadd($set, $member);
    }

    /**
     * @param string $set
     * @param mixed $member
     */
    public function remove($set, $member)
    {
        $this->client->srem($set, $member);
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
