<?php

namespace Raekke\Utils;

/**
 * Object Oriented Array.
 *
 * @package Raekke
 */
class Collection implements \Countable, \IteratorAggregate
{
    protected $elements = array();

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->elements[$key] : $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->elements[$key] = $value;
    }

    /**
     * @return boolean
     */
    public function has($key)
    {
        return isset($this->elements[$key]);
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        unset($this->elements[$key]);
    }

    /**
     * @return array
     */
    public function all()
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * @return integer
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * @return Iterator
     */
    public function getIterator()
    {
        return $this->all();
    }
}
