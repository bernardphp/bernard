<?php

namespace Bernard\Router;

use Bernard\Envelope;
use Bernard\Exception\ReceiverNotFoundException;
use Bernard\Receiver;
use Bernard\Router;

/**
 * Routes an Envelope to a Receiver by an internal receiver map.
 */
class SimpleRouter implements Router
{
    protected $receivers = [];

    /**
     * @param array $receivers
     */
    public function __construct(array $receivers = [])
    {
        foreach ($receivers as $name => $receiver) {
            $this->add($name, $receiver);
        }
    }

    /**
     * @param string $name
     * @param mixed  $receiver
     */
    private function add($name, $receiver)
    {
        if (!$this->accepts($receiver)) {
            throw new \InvalidArgumentException(sprintf('Given "%s" is not supported.', $receiver));
        }

        $this->receivers[$name] = $receiver;
    }

    /**
     * {@inheritdoc}
     */
    public function map(Envelope $envelope)
    {
        $receiver = $this->get($this->getName($envelope));
        $receiver = $this->resolveReceiver($receiver, $envelope);

        if (null === $receiver) {
            throw new ReceiverNotFoundException(sprintf('No receiver found with name "%s".', $envelope->getName()));
        }

        return $receiver;
    }

    /**
     * @param mixed $receiver
     *
     * @return bool
     */
    protected function accepts($receiver)
    {
        return is_callable($receiver) || is_object($receiver) || class_exists($receiver);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function get($name)
    {
        return isset($this->receivers[$name]) ? $this->receivers[$name] : null;
    }

    /**
     * Returns the (message) name to look for in the receiver map.
     *
     * @param Envelope $envelope
     *
     * @return string
     */
    protected function getName(Envelope $envelope)
    {
        return $envelope->getName();
    }

    /**
     * Resolves a receiver or returns null.
     *
     * @param mixed    $receiver
     * @param Envelope $envelope
     *
     * @return Receiver|null
     */
    private function resolveReceiver($receiver, Envelope $envelope)
    {
        if (null === $receiver) {
            return null;
        }

        if ($receiver instanceof Receiver) {
            return $receiver;
        }

        if (is_callable($receiver) == false) {
            $receiver = [$receiver, lcfirst($envelope->getName())];
        }

        // Receiver is still not a callable which means it's not a valid receiver.
        if (is_callable($receiver) == false) {
            return null;
        }

        return new Receiver\CallableReceiver($receiver);
    }
}
