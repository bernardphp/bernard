<?php

namespace Bernard\Driver;

use MongoCollection;
use MongoDate;
use MongoId;

/**
 * Driver supporting MongoDB
 *
 * @package Bernard
 */
class MongoDBDriver implements \Bernard\Driver
{
    private $messages;
    private $queues;

    /**
     * Constructor.
     *
     * @param MongoCollection $queues   Collection where queues will be stored
     * @param MongoCollection $messages Collection where messages will be stored
     */
    public function __construct(MongoCollection $queues, MongoCollection $messages)
    {
        $this->queues = $queues;
        $this->messages = $messages;
    }

    /**
     * {@inheritDoc}
     */
    public function listQueues()
    {
        return $this->queues->distinct('_id');
    }

    /**
     * {@inheritDoc}
     */
    public function createQueue($queueName)
    {
        $data = array('_id' => (string) $queueName);

        $this->queues->update($data, $data, array('upsert' => true));
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        return $this->messages->count(array(
            'queue' => (string) $queueName,
            'visible' => true,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function pushMessage($queueName, $message)
    {
        $data = array(
            'queue'   => (string) $queueName,
            'message' => (string) $message,
            'sentAt'  => new MongoDate(),
            'visible' => true,
        );

        $this->messages->insert($data);
    }

    /**
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        $runtime = microtime(true) + $interval;

        while (microtime(true) < $runtime) {
            $result = $this->messages->findAndModify(
                array('queue' => (string) $queueName, 'visible' => true),
                array('$set' => array('visible' => false)),
                array('message' => 1),
                array('sort' => array('sentAt' => 1))
            );

            if ($result) {
                return array((string) $result['message'], (string) $result['_id']);
            }

            usleep(10000);
        }

        return array(null, null);
    }

    /**
     * {@inheritDoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $this->messages->remove(array(
            '_id' => new MongoId((string) $receipt),
            'queue' => (string) $queueName,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        $cursor = $this->messages->find(
                array('queue' => (string) $queueName, 'visible' => true),
                array('_id' => 0, 'message' => 1)
            )
            ->sort(array('sentAt' => 1))
            ->limit($limit)
            ->skip($index)
        ;

        return array_map(
            function ($result) { return (string) $result['message']; },
            iterator_to_array($cursor, false)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function removeQueue($queueName)
    {
        $this->queues->remove(array('_id' => $queueName));
        $this->messages->remove(array('queue' => (string) $queueName));
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
        return array(
            'messages' => (string) $this->messages,
            'queues' => (string) $this->queues,
        );
    }
}
