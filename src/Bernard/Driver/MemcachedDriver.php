<?php

namespace Bernard\Driver;

use Bernard\Driver;
use Bernard\Exception\DriverException;

/**
 * Memcached driver for bernard.
 *
 * @package Bernard
 */
class MemcachedDriver implements Driver
{   
    /**
     * @var \Memcached $memcached Memcache server(s) connection 
     */
    protected $memcached;

    /** 
     * @var integer $ttl Lifetime for items created by the driver
     */
    protected $ttl = 0;

    /** 
     * @var string $namespace All keys created by the driver will have this prefix
     */
    protected $namespace;

    /**
     * Pop message threshold is the number of seconds to wait in popMessage 
     * before assuming that a pushMessage was terminated before adding the 
     * message, but after incremeting the tail. 
     *
     * @var double $popMessageThreshold 
     */ 
    protected $popMessageThreshold;

    /**
     * @param Memcached $memcached
     * @param string $namespace
     * @param double $popMessageThreshold  
     */
    public function __construct(\Memcached $memcached, $namespace = 'bernard', $popMessageThreshold = 1.0)
    {
        $this->memcached = $memcached;
        $this->namespace = $namespace;

        $this->popMessageThreshold = $popMessageThreshold;
    }

    /**
     * @return string
     */
    protected function getQueueListKey()
    {
        return sprintf('%s_queuelist', $this->namespace);
    }

    /**
     * @param string $queueName
     * @return string
     */
    protected function getQueueHeadKey($queueName)
    {
        return sprintf('%s_%s_head', $this->namespace, $queueName);
    }

    /**
     * @param string $queueName
     * @return string
     */
    protected function getQueueTailKey($queueName)
    {
        return sprintf('%s_%s_tail', $this->namespace, $queueName);
    }

    /**
     * @param string $queueName
     * @param integer $itemId
     * @return string
     */
    protected function getQueueItemKey($queueName, $itemId)
    {
        return sprintf('%s_%s_item_%u', $this->namespace, $queueName, $itemId);
    }

    /**
     * @param string $queueName
     * @return boolean
     */
    protected function addQueueNameToQueueList($queueName)
    {
        $casToken = 0.0;

        $queueList = $this->memcached->get($this->getQueueListKey(), null, $casToken);
        if ($queueList === false) {
            return $this->memcached->set($this->getQueueListKey(), json_encode(array($queueName)));
        }

        $queueNames = json_decode($queueList, true);
        if (in_array($queueName, $queueNames)) {
            return true;
        }

        $queueNames[] = $queueName;
        return $this->memcached->cas($casToken, $this->getQueueListKey(), json_encode(array_values($queueNames)), $this->ttl);
    }

    /** 
     * @param string $queueName
     * @return boolean
     */
    protected function removeQueueNameFromQueueList($queueName)
    {
        $casToken = 0.0;

        $queueList = $this->memcached->get($this->getQueueListKey(), null, $casToken);
        if ($queueList === false) {
            return true;
        }

        $queueNames = json_decode($queueList, true);

        $index = array_search($queueName, $queueNames);
        if ($index === false) {
            return true;
        }

        unset($queueNames[$index]);

        return $this->memcached->cas($casToken, $this->getQueueListKey(), json_encode(array_values($queueNames)), $this->ttl);
    }

    /**
     * Returns a list of all queue names.
     *
     * @return array
     */
    public function listQueues()
    {
        $queueList = $this->memcached->get($this->getQueueListKey());

        return json_decode($queueList ?: '[]', true);
    }

    /**
     * Create a queue.
     *
     * @param string $queueName
     */
    public function createQueue($queueName)
    {
        // Ensure that the queue is in the list
        $this->addQueueNameToQueueList($queueName);

        // Initialize the queue
        // Get the values for head and tail
        $head = $this->memcached->get($this->getQueueHeadKey($queueName));
        $tail = $this->memcached->get($this->getQueueTailKey($queueName));

        // If both head and tail are false, then the queue is not created.
        // It is initialized with both head and tail set to 1
        if ($head === false && $tail === false) {
            $this->memcached->add($this->getQueueHeadKey($queueName), 0, $this->ttl);
            $this->memcached->add($this->getQueueTailKey($queueName), 0, $this->ttl);

            return;
        }

        // If only head is not set, something strange might have happened
        // Intialize the head to 0 (this assumes that tail will never be lower than 0)
        if ($head === false) {
            $this->memcached->add($this->getQueueHeadKey($queueName), 0, $this->ttl);

            return;
        }

        // If tail is not set, initialize tail to head
        // eg. the queue is empty
        if ($tail === false) {
            $this->memcached->add($this->getQueueTailKey($queueName), $head, $this->ttl);

            return;
        }
    }

    /**
     * Count the number of messages in queue. This can be a approximately number.
     *
     * @return integer
     */
    public function countMessages($queueName)
    {
        $head = $this->memcached->get($this->getQueueHeadKey($queueName));
        $tail = $this->memcached->get($this->getQueueTailKey($queueName));

        return max(0, $tail - $head);
    }

    /**
     * Insert a message at the top of the queue.
     *
     * @param string $queueName
     * @param string $message
     */
    public function pushMessage($queueName, $message)
    {
        // Increment tail (this operation is atomic)
        $nextItemId = $this->memcached->increment($this->getQueueTailKey($queueName)) - 1;
        if ($nextItemId === false) {
            throw new DriverException('Unable to increment tail for queue: "' . $queueName . '"');
        }

        // Attempt to add the message using the next id
        $itemKey = $this->getQueueItemKey($queueName, $nextItemId);
        if ($this->memcached->add($itemKey, $message, $this->ttl)) {
            return;
        }

        // In case of failure the tail must be decremented again
        if ($this->memcached->decrement($this->getQueueTailKey($queueName))) {
            throw new DriverException('Unable to queue item: "' . $itemKey . '"');
        }

        // Total failure
        throw new DriverException('Unable to queue item: "' . $itemKey . '", and failed to decrement tail on failure');
    }

    /**
     * Remove the next message in line. And if no message is available
     * wait $interval seconds.
     *
     * @param  string  $queueName
     * @param  integer $interval
     * @return array   An array like array($message, $receipt);
     */
    public function popMessage($queueName, $interval = 5)
    {
        $headCasToken = 0;

        // Read head and tail. Additionally we store a check and set token (cas)
        // for head so we can update it atomically later.
        $head = $this->memcached->get($this->getQueueHeadKey($queueName), null, $headCasToken);
        $tail = $this->memcached->get($this->getQueueTailKey($queueName));

        // Check if the queue has been properly initialized
        if ($head === false || $tail === false) {
            throw new DriverException('Undefined head or tail for queue: "' . $queueName . '"');
        }

        // Head and tail and equal, this means the queue is empty
        if ($head >= $tail) {
            return null;
        }

        // Get the key corrosponding to the next item
        $itemKey = $this->getQueueItemKey($queueName, $head);
        $message = false;

        // Attempt to retrieve the message for popMessageThreshold seconds.
        //
        // Normally this will complete instantly, but this allows
        // pushMessage time to complete its insert before skipping
        // the message.
        $waitUntil = microtime(true) + $this->popMessageThreshold;
        do {
            if ((($message = $this->memcached->get($itemKey))) !== false) {
                break;
            }
            usleep(100);
        } while (microtime(true) < $waitUntil);

        // We have waited long enough for the push to complete but no message emerged.
        // In this case we assume that pushMessage has terminated before the message
        // was added under $itemKey. We skip the message by moving head forward.
        if ($message === false) {
            $this->memcached->cas($headCasToken, $this->getQueueHeadKey($queueName), $head + 1, $this->ttl);
            
            return null;
        }

        // Update the head token (cas increment)
        // 
        // If this fails, another consume has popped the message before
        // us. Return nothing.
        if ($this->memcached->cas($headCasToken, $this->getQueueHeadKey($queueName), $head + 1, $this->ttl) === false) {
            return null;
        }

        // Delete the message
        if ($this->memcached->delete($itemKey) === false) {
            //throw new \Exception('Unable to delete message: "' . $itemKey . '"');
        }

        return array($message, $itemKey);
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
        // not implemented for memcached
    }

    /**
     * Returns a $limit numbers of messages without removing them
     * from the queue.
     *
     * @param string  $queueName
     * @param integer $index
     * @param integer $limit
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        $head = $this->memcached->get($this->getQueueHeadKey($queueName));
        $tail = $this->memcached->get($this->getQueueTailKey($queueName));

        if ($head >= $tail) {
            return array();
        }

        // Start at head + index, but never exceed tail
        // End at start + limit, but never exceed tail
        $itemIdStart = min($head + $index, $tail);
        $itemIdEnd = min($head + $index + $limit, $tail);

        $itemKeys = array();
        foreach (range($itemIdStart, $itemIdEnd) as $id) {
            $itemKeys[] = $this->itemKey($id);
        }

        return $this->memcached->getMulti($itemKeys);
    }

    /**
     * Removes the queue.
     *
     * @param string $queueName
     */
    public function removeQueue($queueName)
    {
        $keys = $this->memcached->getAllKeys();
        if ($keys === false) {
            throw new DriverException('Unable to retrieve keys from memcached');
        }

        $filter = sprintf('%s_%s_', $this->namespace, $queueName);
        $keys = array_filter($keys, function($key) use ($filter) {
            return strpos($key, $filter) === 0;
        });

        if ($this->memcached->deleteMulti(array_values($keys)) === false) {
            return;
        }

        $this->removeQueueNameFromQueueList($queueName);
    }

    /**
     * @return array
     */
    public function info()
    {
        $info = array();

        foreach ($this->memcached->getServerList() as $index => $server) {
            $info['memcached_server[' . $index . ']_host'] = $server['host'];
            $info['memcached_server[' . $index . ']_port'] = $server['port'];
        }

        $info['memcached_ttl'] = $this->ttl;
        $info['namespace'] = $this->namespace;

        return $info;
    }
}
