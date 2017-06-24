<?php

namespace Bernard\Router;

use Bernard\Envelope;
use Bernard\Exception\ReceiverNotFoundException;

/**
 * Routes a Envelope to a Receiver by using the name of the Envelope.
 *
 * @package Bernard
 */
class SimpleRouter implements \Bernard\Router
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
    public function add($name, $receiver)
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
        $receiver = $this->get($envelope->getName());

        if (null === $receiver) {
            throw new ReceiverNotFoundException(sprintf('No receiver found with name "%s".', $envelope->getName()));
        }

        if (is_callable($receiver)) {
            return $receiver;
        }

        return array($receiver, lcfirst($envelope->getName()));
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
}
