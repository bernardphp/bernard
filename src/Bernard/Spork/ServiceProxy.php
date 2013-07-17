<?php

namespace Bernard\Spork;

use Bernard\Message;
use Bernard\Spork\Exception\ProcessException;
use Spork\Fork;
use Spork\ProcessManager;

/**
 * @package Bernard
 */
class ServiceProxy
{
    protected $spork;
    protected $callable;

    /**
     * @param ProcessManager $manager
     * @param callable       $callable
     */
    public function __construct(ProcessManager $manager, $callable)
    {
        $this->spork = $manager;
        $this->callable = $callable;
    }

    /**
     * Throws an Exception based on the $error array given by Spork\ProcessManager.
     * It hides some information but the trade of are still in the positive.
     *
     * @param  Fork                  $fork
     * @throws ForkingLogicException
     */
    public function fail(Fork $fork)
    {
        $error = $fork->getError();

        // Spork changed between two versions.
        if (is_array($error)) {
            throw new ProcessException(
                $error['class'],
                $error['message'],
                $error['file'],
                $error['line'],
                $error['code']
            );
        }

        throw new ProcessException(
            $error->getClass(),
            $error->getMessage(),
            $error->getFile(),
            $error->getLine(),
            $error->getCode()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(Message $message)
    {
        $callable = $this->callable;

        $fork = $this->spork->fork(function () use ($callable, $message) {
            call_user_func($callable, $message);
        });

        $fork->wait();
        $fork->fail(array($this, 'fail'));
    }
}
