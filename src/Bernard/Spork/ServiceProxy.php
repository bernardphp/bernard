<?php

namespace Bernard\Spork;

use Bernard\Spork\Exception\ProcessException;
use Spork\Fork;
use Spork\ProcessManager;
use Bernard\Message\Envelope;

/**
 * @package Bernard
 */
class ServiceProxy
{
    protected $spork;
    protected $object;

    /**
     * @param ProcessManager $manager
     * @param object        $object
     */
    public function __construct(ProcessManager $manager, $object)
    {
        $this->spork = $manager;
        $this->object = $object;
    }

    /**
     * Throws an Exception based on the $error array given by Spork\ProcessManager.
     * It hides some information but the trade of are still in the positive.
     *
     * @param  Fork                  $fork
     * @throws ForkingLogicException
     */
    public function __fail__(Fork $fork)
    {
        $error = $fork->getError();

        throw new ProcessException($error->getClass(), $error->getMessage(), $error->getFile(), $error->getLine(), $error->getCode());
    }

    /**
     * {@inheritDoc}
     */
    public function __call($method, array $arguments = array())
    {
        // The arguments will hold a single key 0 with an instance of Envelope
        $callable = array($this->object, $method);

        $fork = $this->spork->fork(function () use ($callable, $arguments) {
            call_user_func_array($callable, $arguments);
        });

        // Wait for the fork, blocks the process.
        // To avoid collisions with the object being proxied.
        $fork->wait();
        $fork->fail(array($this, '__fail__'));
    }

}
