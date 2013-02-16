<?php

namespace Raekke\Driver;

use Predis\Client;

/**
 * ext-redis wrapper.
 *
 * @package Raekke
 */
class Connection
{
    protected $client;
    protected $configuration;

    /**
     * @param string|array|Client $server
     * @param Configuration $configuration
     */
    public function __construct($server = null, Configuration $configuration = null)
    {
        $this->configuration = $configuration ?: new Configuration;

        if (false == $server instanceof Client) {
            $server = new Client($server, array('prefix' => $this->configuration->getPrefix()));
        }

        $this->client = $server;
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
