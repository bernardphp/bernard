<?php

namespace Bernard\Message;

use Bernard\Message;

/**
 * Simple message that gets you started.
 * It has a name and an array of arguments.
 * It does not enforce any types or properties so be careful on relying them
 * being there.
 */
final class PlainMessage implements Message, \ArrayAccess
{
    private $name;
    private $arguments;

    /**
     * @param string $name
     * @param array  $arguments
     */
    public function __construct($name, array $arguments = [])
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->arguments;
    }

    /**
     * Returns the argument if found or null.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->has($name) ? $this->arguments[$name] : null;
    }

    /**
     * Checks whether the arguments contain the given key.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->arguments);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Message is immutable');
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException('Message is immutable');
    }

    public function __get($property)
    {
        return $this->get($property);
    }

    public function __isset($property)
    {
        return $this->has($property);
    }

    public function __set($property, $value)
    {
        throw new \LogicException('Message is immutable');
    }

    public function __unset($property)
    {
        throw new \LogicException('Message is immutable');
    }
}
