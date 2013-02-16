<?php

namespace Raekke\Driver;

use Raekke\Util\Collection;

/**
 * Configuration class. Contains metadata for the project.
 *
 * @package Raekke
 */
class Configuration
{
    protected $attributes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes = new Collection;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->attributes->set('prefix', $prefix);
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->attributes->get('prefix', 'raekke');
    }
}
