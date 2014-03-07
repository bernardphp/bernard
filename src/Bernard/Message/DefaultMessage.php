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
    public function __construct($name, array $arguments = array())
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function all()
    {
        return $this->arguments;
    }

    public function get($name)
    {
        return $this->offsetGet($name);
    }

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

    public function offsetSet($offset, $value) { }

    public function offsetUnset($offset) { }

    public function getName()
    {
        return $this->name;
    }
}
