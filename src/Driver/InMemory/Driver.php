<?php

namespace Bernard\Driver\InMemory;

/**
 * Simple in-memory driver.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class Driver implements \Bernard\Driver
{
    private $queues = [];

    /**
     * {@inheritdoc}
     */
    public function listQueues()
    {
        return array_keys($this->queues);
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue($queueName)
    {
        if (!array_key_exists($queueName, $this->queues)) {
            $this->queues[$queueName] = [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countMessages($queueName)
    {
        if (array_key_exists($queueName, $this->queues)) {
            return count($this->queues[$queueName]);
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function pushMessage($queueName, $message)
    {
        $this->queues[$queueName][] = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function popMessage($queueName, $duration = 5)
    {
        if (!array_key_exists($queueName, $this->queues) || count($this->queues[$queueName]) < 1) {
            return [null, null];
        }

        return [array_shift($this->queues[$queueName]), null];
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        // Noop
    }

    /**
     * {@inheritdoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        if (array_key_exists($queueName, $this->queues)) {
            return array_slice($this->queues[$queueName], $index, $limit);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function removeQueue($queueName)
    {
        if (array_key_exists($queueName, $this->queues)) {
            unset($this->queues[$queueName]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        return [];
    }
}
