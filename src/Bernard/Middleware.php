<?php

namespace Bernard;

/**
 * Builder of middleware. Takes any number of Callables and handles
 * them as factories returning a new invokable.
 *
 * @package Raven
 */
class Middleware
{
    protected $factories;

    /**
     * @param callable[] $factories
     */
    public function __construct(array $factories = array())
    {
        $this->factories = array();

        array_map(array($this, 'add'), $factories);
    }

    /**
     * @param callable $factory
     */
    public function add($factory)
    {
        Assert::assertCallable($factory);

        $this->factories[] = $factory;
    }

    /**
     * Creates the chain a returns the wrapped callable.
     *
     * @param callable $callable
     * @return callable
     */
    public function wrap($callable)
    {
        Assert::assertCallable($callable);

        $factories = $this->factories;

        array_reverse($factories);

        return array_reduce($factories, array($this, 'reduce'), $callable);
    }

    /**
     * Reduces the $factory and $callable into a single
     * $callable and effectively creating a chain.
     *
     * @param callable $callable
     * @param callable $factory
     */
    public function reduce($callable, $factory)
    {
        Assert::assertCallable($factory);
        Assert::assertCallable($callable);

        return $factory($callable);
    }
}
