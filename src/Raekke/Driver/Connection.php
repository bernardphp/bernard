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
        $this->configuration = $configuration;
        $this->client = new Client($server, array('prefix' => $configuration));
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
