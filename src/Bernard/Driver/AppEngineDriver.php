<?php

namespace Bernard\Driver;

use google\appengine\api\taskqueue\PushTask;

/**
 * Simple driver for google AppEngine. Many features are not supported.
 * It takes a list of array('name' => 'endpoint') to route messages to the
 * correct place.
 *
 * @package Bernard
 */
class AppEngineDriver implements \Bernard\Driver
{
    protected $queueMap;

    /**
     * @param array $queueMap
     */
    public function __construct(array $queueMap)
    {
        $this->queueMap = $queueMap;
    }

    /**
     * {@inheritDoc}
     */
    public function pushMessage($queueName, $message)
    {
        $task = new PushTask($this->resolveEndpoint($queueName), compact('message'));
        $task->add($queueName);
    }

    /**
     * {@inheritDoc}
     */
    public function listQueues()
    {
        return array_flip($this->queueMap);
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function createQueue($queueName) { }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName) { }

    /**
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5) { }

    /**
     * {@inheritDoc}
     */
    public function acknowledgeMessage($queueName, $receipt) { }

    /**
     * {@inheritDoc}
     */
    public function removeQueue($queueName) { }

    /**
     * @param  string                   $queueName
     * @throws InvalidArgumentException
     */
    protected function resolveEndpoint($queueName)
    {
        if (isset($this->queueMap[$queueName])) {
            return $this->queueMap[$queueName];
        }

        return '/_ah/queue/' . $queueName;
    }
}
