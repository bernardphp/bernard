<?php

namespace Bernard;

/**
 * @package Bernard
 */
interface Driver
{
    /**
     * Returns a list of all queue names.
     *
     * @return array
     */
    public function listQueues();

    /**
     * Create a queue.
     *
     * @param string $queueName
     */
    public function createQueue($queueName);

    /**
     * Count the number of messages in queue. This can be a approximately number.
     *
     * @return int
     */
    public function countMessages($queueName);

    /**
     * Insert a message at the top of the queue.
     *
     * @param string $queueName
     * @param string $message
     */
    public function pushMessage($queueName, $message);

    /**
     * Remove the next message in line. And if no message is available
     * wait $duration seconds.
     *
     * @param string $queueName
     * @param int    $duration
     *
     * @return array An array like array($message, $receipt);
     */
    public function popMessage($queueName, $duration = 5);

    /**
     * If the driver supports it, this will be called when a message
     * have been consumed.
     *
     * @param string $queueName
     * @param mixed  $receipt
     */
    public function acknowledgeMessage($queueName, $receipt);

    /**
     * Returns a $limit numbers of messages without removing them
     * from the queue.
     *
     * @param string $queueName
     * @param int    $index
     * @param int    $limit
     */
    public function peekQueue($queueName, $index = 0, $limit = 20);

    /**
     * Removes the queue.
     *
     * @param string $queueName
     */
    public function removeQueue($queueName);

    /**
     * @return array
     */
    public function info();
}
