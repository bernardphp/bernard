<?php

namespace Bernard\Middleware;

use Bernard\Verify;
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

        array_walk($factories, array($this, 'push'));
    }

    /**
     * @param callable $factory
     */
    public function push($factory)
    {
        Verify::isCallable($factory);

        $this->factories->push($factory);
    }

    /**
     * @param callable $factory
     */
    public function unshift($factory)
    {
        Verify::isCallable($factory);

        $this->factories->unshift($factory);
    }

    /**
     * Creates the chain a returns the wrapped callable.
     *
     * @param  Middleware $middleware
     * @return Middleware
     */
    public function build(Middleware $middleware)
    {
        $this->factories->rewind();

        $callback = function (Middleware $middleware, $factory) {
            return $factory($middleware);
        };

        return array_reduce(iterator_to_array($this->factories), $callback, $middleware);
    }
}
