<?php

namespace Raekke\Driver;

use Raekke\Util\ArrayCollection;

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
        $this->attributes = new ArrayCollection;
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
