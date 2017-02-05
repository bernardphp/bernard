<?php

namespace Bernard\Driver;

use Bernard\Driver;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PhpAmqpDriver implements Driver
{
    /**
     * @var AbstractConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var string
     */
    private $exchange;
    /**
     * @var array|null
     */
    private $defaultMessageParams;

    /**
     * @param AbstractConnection $connection
     * @param string             $exchange
     * @param array              $defaultMessageParams
     */
    public function __construct(AbstractConnection $connection, $exchange, array $defaultMessageParams = null)
    {
        $this->connection = $connection;
        $this->exchange = $exchange;
        $this->defaultMessageParams = $defaultMessageParams;
    }

    /**
     * Returns a list of all queue names.
     *
     * @return array
     */
    public function listQueues()
    {
    }

    /**
     * Create a queue.
     *
     * @param string $queueName
     */
    public function createQueue($queueName)
    {
        $channel = $this->getChannel();
        $channel->exchange_declare($this->exchange, 'direct', false, true, false);
        $channel->queue_declare($queueName, false, true, false, false);
        $channel->queue_bind($queueName, $this->exchange, $queueName);
    }

    /**
     * Count the number of messages in queue. This can be a approximately number.
     *
     * @param string $queueName
     *
     * @return int
     */
    public function countMessages($queueName)
    {
        list(,$messageCount) = $this->getChannel()->queue_declare($queueName, true);

        return $messageCount;
    }

    /**
     * Insert a message at the top of the queue.
     *
     * @param string $queueName
     * @param string $message
     */
    public function pushMessage($queueName, $message)
    {
        $amqpMessage = new AMQPMessage($message, $this->defaultMessageParams);
        $this->getChannel()->basic_publish($amqpMessage, $this->exchange, $queueName);
    }

    /**
     * Remove the next message in line. And if no message is available
     * wait $duration seconds.
     *
     * @param string $queueName
     * @param int    $duration
     *
     * @return array An array like array($message, $receipt);
     */
    public function popMessage($queueName, $duration = 5)
    {
        $runtime = microtime(true) + $duration;

        while (microtime(true) < $runtime) {
            $message = $this->getChannel()->basic_get($queueName);

            if ($message) {
                return [$message->body, $message->get('delivery_tag')];
            }

            // sleep for 10 ms to prevent hammering CPU
            usleep(10000);
        }

        return [null, null];
    }

    /**
     * If the driver supports it, this will be called when a message
     * have been consumed.
     *
     * @param string $queueName
     * @param mixed  $receipt
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $this->getChannel()->basic_ack($receipt);
    }

    /**
     * Returns a $limit numbers of messages without removing them
     * from the queue.
     *
     * @param string $queueName
     * @param int    $index
     * @param int    $limit
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
    }

    /**
     * Removes the queue.
     *
     * @param string $queueName
     */
    public function removeQueue($queueName)
    {
        $this->getChannel()->queue_delete($queueName);
    }

    /**
     * @return array
     */
    public function info()
    {
    }

    public function __destruct()
    {
        if (null !== $this->channel) {
            $this->channel->close();
        }
    }

    private function getChannel()
    {
        if (null === $this->channel) {
            $this->channel = $this->connection->channel();
        }

        return $this->channel;
    }
}
