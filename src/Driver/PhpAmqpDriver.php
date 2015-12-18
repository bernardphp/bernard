<?php

namespace Bernard\Driver;

use Bernard\Driver;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PhpAmqpDriver implements Driver
{
    /**
     * @var AMQPStreamConnection
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
     * @param AMQPStreamConnection $connection
     * @param string               $exchange
     * @param array                $defaultMessageParams
     */
    public function __construct(AMQPStreamConnection $connection, $exchange, array $defaultMessageParams = null)
    {
        $this->connection = $connection;
        $this->exchange = $exchange;
        $this->defaultMessageParams = $defaultMessageParams;

        $this->channel = $this->connection->channel();
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
        $this->channel->exchange_declare($this->exchange, 'direct', false, true, false);
        $this->channel->queue_declare($queueName, false, true, false, false);
        $this->channel->queue_bind($queueName, $this->exchange);
    }

    /**
     * Count the number of messages in queue. This can be a approximately number.
     *
     * @return int
     */
    public function countMessages($queueName)
    {
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
        $this->channel->basic_publish($amqpMessage, $this->exchange);
    }

    /**
     * Remove the next message in line. And if no message is available
     * wait $interval seconds.
     *
     * @param string $queueName
     * @param int    $interval
     *
     * @return array An array like array($message, $receipt);
     */
    public function popMessage($queueName, $interval = 5)
    {
        $message = $this->channel->basic_get($queueName);
        if (!$message) {
            // sleep for 10 ms to prevent hammering CPU
            usleep(10000);

            return [null, null];
        }

        return [$message->body, $message->get('delivery_tag')];
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
        $this->channel->basic_ack($receipt);
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
    }

    /**
     * @return array
     */
    public function info()
    {
    }

    public function __destruct()
    {
        $this->channel->close();
    }
}
