<?php

namespace Raekke;

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
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->attributes->set('namespace', $namespace);
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->attributes->get('namespace', 'raekke');
    }
}
