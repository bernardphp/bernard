<?php

declare(strict_types=1);

namespace Bernard\Message;

use Bernard\Message;

/**
 * Simple message that gets you started.
 * It has a name and an array of arguments.
 * It does not enforce any types or properties so be careful relying on them
 * being there.
 */
final class PlainMessage implements Message, \ArrayAccess
{
    private string $name;
    private array $arguments;

    public function __construct(string $name, array $arguments = [])
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return $this->name;
    }

    public function all() : array
    {
        return $this->arguments;
    }

    /**
     * Returns the argument if found or null.
     */
    public function get(string $name) : mixed
    {
        return $this->has($name) ? $this->arguments[$name] : null;
    }

    /**
     * Checks whether the arguments contain the given key.
     */
    public function has(string $name) : bool
    {
        return \array_key_exists($name, $this->arguments);
    }

    public function offsetGet(mixed $offset) : mixed
    {
        return $this->get($offset);
    }

    public function offsetExists(mixed $offset) : bool
    {
        return $this->has($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('Message is immutable');
    }

    public function offsetUnset(mixed $offset): void
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

    public function __set($property, $value): void
    {
        throw new \LogicException('Message is immutable');
    }

    public function __unset($property): void
    {
        throw new \LogicException('Message is immutable');
    }
}
