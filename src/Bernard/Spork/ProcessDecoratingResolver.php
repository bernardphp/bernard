<?php

namespace Bernard\Spork;

use Bernard\Message\Envelope;
use Bernard\ServiceResolver;
use Spork\ProcessManager;

/**
 * Decorates an ordinary ServiceResolver and will return ForkingInvoker
 * for the resolve method.
 *
 * @see ForkingInvoker
 * @package Bernard
 */
class ProcessDecoratingResolver implements \Bernard\ServiceResolver
{
    protected $spork;
    protected $resolver;

    /**
     * @param ProcessManager $manager
     * @param Invoker        $invoker
     */
    public function __construct(ProcessManager $manager, ServiceResolver $resolver)
    {
        $this->spork = $manager;
        $this->resolver = $resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function register($name, $service)
    {
        $this->resolver->register($name, $service);
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Envelope $envelope)
    {
        return new ProcessInvoker($this->spork, $this->resolver->resolve($envelope));
    }
}
