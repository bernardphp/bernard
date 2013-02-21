<?php

namespace Raekke\Queue;

use Raekke\Message\MessageInterface;
use Raekke\Message\MessageWrapper;
use Raekke\Serializer\Serializer;
use Raekke\Util\ArrayCollection;
use Raekke\QueueManager;

/**
 * @package Raekke
 */
class Queue implements \Countable
{
    protected $key;
    protected $manager;
    protected $closed;

    public function __construct($name, QueueManager $manager)
    {
        $this->closed     = false;
        $this->key        = 'queue:' . $name;
        $this->name       = $name;
        $this->manager    = $manager;
    }

    public function attach()
    {
        $this->errorIfClosed();

        $this->manager->getConnection()->insert('queues', $this->name);
    }

    public function count()
    {
        $this->errorIfClosed();

        return $this->manager->getConnection()->count($this->key);
    }

    public function push(MessageInterface $message)
    {
        $this->errorIfClosed();

        $wrapper = new MessageWrapper($message);

        $payload = $this->manager->getSerializer()->serialize($wrapper);
        $this->manager->getConnection()->push($this->key, $payload);
    }

    public function close()
    {
        $this->errorIfClosed();

        $this->closed = true;

        $this->manager->getConnection()->remove('queues', $this->name);
        $this->manager->getConnection()->delete($this->key);

        return $this->closed;
    }

    public function peek($index, $length)
    {
        $this->errorIfClosed();

        $messages = $this->manager->getConnection()->slice($this->key, $index, $length);
        $messages = new ArrayCollection($messages);

        $serializer = $this->manager->getSerializer();

        return $messages->map(function ($payload) use ($serializer) {
            return $serializer->deserialize($payload, false);
        });
    }

    public function pop($interval = 5)
    {
        if (null === $message = $this->manager->getConnection()->pop($this->key, $interval)) {
            return null;
        }

        return $this->manager->getSerializer()->deserialize($message);
    }

    public function isClosed()
    {
        return $this->closed;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getManager()
    {
        return $this->manager;
    }

    protected function errorIfClosed()
    {
        if ($this->closed) {
            throw new \LogicException('The Queue is closed.');
        }
    }
}
