<?php

namespace Bernard\Middleware;

use Bernard\Assert;
use Bernard\Middleware;

/**
 * Builder of middleware. Takes any number of Callables and handles
 * them as factories returning a new invokable.
 *
 * @package Bernard
 */
class MiddlewareBuilder
{
    protected $factories;

    /**
     * @param callable[] $factories
     */
    public function __construct(array $factories = array())
    {
        $this->factories = new \SplStack;

        array_map(array($this, 'push'), $factories);
    }

    /**
     * @param callable $factory
     */
    public function push($factory)
    {
        Assert::assertCallable($factory);

        $this->factories->push($factory);
    }

    /**
     * @param callable $factory
     */
    public function unshift($factory)
    {
        Assert::assertCallable($factory);

        $this->factories->unshift($factory);
    }

    /**
     * Creates the chain a returns the wrapped callable.
     *
     * @param Middleware $middleware
     * @return Middleware
     */
    public function build(Middleware $middleware)
    {
        $this->factories->rewind();

        $factories = iterator_to_array($this->factories);

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
