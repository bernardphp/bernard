<?php

namespace Bernard\Spork;

use Bernard\ServiceResolver\Invocator;
use Bernard\Spork\Exception\ProcessException;
use Spork\Fork;
use Spork\ProcessManager;

/**
 * Wraps a Invocator object and forks out when executing the service. This
 * will help on memory. If memory is exceeded it will kill the fork and not
 * the master process.
 *
 * @package Bernard
 */
class ProcessInvocator extends \Bernard\ServiceResolver\Invocator
{
    protected $invocator;
    protected $spork;

    /**
     * @param ProcessManager $manager
     * @param Invocator $invocator
     */
    public function __construct(ProcessManager $manager, Invocator $invocator)
    {
        $this->invocator = $invocator;
        $this->spork = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function invoke()
    {
        $fork = $this->spork->fork($this->invocator);
        $fork->fail(array($this, 'fail'));
        $fork->wait();
    }

    /**
     * Throws an Exception based on the $error array given by Spork\ProcessManager.
     * It hides some information but the trade of are still in the positive.
     *
     * @param Fork $fork
     * @throws ForkingLogicException
     */
    public function fail(Fork $fork)
    {
        list($class, $message, $file, $line, $code) = $fork->getError();

        throw new ProcessException($class, $message, $file, $line, $code);
    }
}
