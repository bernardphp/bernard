<?php

namespace Bernard\Message;

use ArrayAccess;

/**
 * Simple message that gets you started. It has a name an a array of arguments
 * It does not enforce any types or properties so be careful on relying them
 * being there.
 *
 * @package Bernard
 */
class DefaultMessage extends AbstractMessage implements ArrayAccess
{
    protected $name;
    protected $arguments;

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
     * @return array
     */
    public function all()
    {
        return $this->arguments;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return $this->offsetExists($name);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->arguments);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->arguments[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Message is immutable');
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException('Message is immutable');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    public function __get($property)
    {
        return $this->offsetGet($property);
    }

    public function __set($property, $value)
    {
        $this->offsetSet($property, $value);
    }
}
