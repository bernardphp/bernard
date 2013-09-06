<?php

namespace Bernard\Middleware;

use Bernard\Assert;
use Bernard\Middleware;

/**
 * Builder of middleware. Takes any number of Callables and handles
 * them as factories returning a new invokable.
 *
 * @package Raven
 */
class MiddlewareChain
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
     * @param Middleware $middleware
     * @return Middleware
     */
    public function chain(Middleware $middleware)
    {
        reset($this->factories);

        $factories = array_reverse($this->factories);

        return array_reduce($factories, array($this, 'reduce'), $middleware);
    }

    /**
     * Reduces the $factory and $callable into a single
     * $callable and effectively creating a chain.
     *
     * @param Middleware $middleware
     * @param callable $factory
     */
    public function reduce(Middleware $middleware, $factory)
    {
        Assert::assertCallable($factory);

        return $factory($middleware);
    }
}
