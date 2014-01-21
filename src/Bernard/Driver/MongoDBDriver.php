<?php

namespace Bernard\Driver;

/**
 * Driver supporting MongoDB
 *
 * @package Bernard
 */
class MongoDBDriver implements \Bernard\Driver
{
    protected $db;
    protected $collection;
    protected $queueCollection;

    /**
     * {@inheritDoc}
     */
    public function __construct(\MongoDB $db)
    {
        $this->db = $db;
        $this->collection = $db->selectCollection('bernardMessages');
        $this->queueCollection = $db->selectCollection('bernardQueues');
    }

    /**
     * {@inheritDoc}
     */
    public function listQueues()
    {
        return $this->queueCollection->distinct('queue');
    }

    /**
     * {@inheritDoc}
     */
    public function createQueue($queueName)
    {
        $query = array('queue' => $queueName);
        $this->queueCollection->update($query, $query, array('upsert' => true));
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        return $this->collection->count(array('queue' => $queueName));
    }

    /**
     * {@inheritDoc}
     */
    public function pushMessage($queueName, $message)
    {
        $data = array(
            'queue'   => $queueName,
            'message' => $message,
            'sentAt'  => microtime(),
            'visible' => true,
        );

        $this->collection->insert($data);
    }

    /**
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        $runtime = microtime(true) + $interval;
        $query = array('queue' => $queueName, 'visible' => true);
        $update = array('$set' => array('visible' => false));
        $options = array('sort' => array('sentAt' => 1), 'new' => true);

        while (microtime(true) < $runtime) {
            if ($result = $this->collection->findAndModify($query, $update, array(), $options)) {
                return array($result['message'], (string) $result['_id']);
            }

            //sleep for 10 ms
            usleep(10000);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $this->collection->remove(array('_id' => new \MongoId($receipt)));
    }

    /**
     * {@inheritDoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        $query = array(
            '$orderby'  => array('sentAt' => 1),
            '$query' => array('queue' => $queueName, 'visible' => true),
        );
        $fields = array(
            'message' => 1,
            '_id' => 0,
        );

        $cursor = $this->collection->find($query, $fields)
            ->limit($limit)
            ->skip($index);

        return array_map(function ($item) { return $item['message']; }, iterator_to_array($cursor));
    }

    /**
     * {@inheritDoc}
     */
    public function removeQueue($queueName)
    {
        $this->collection->remove(array('queue' => $queueName));
        $this->queueCollection->remove(array('queue' => $queueName));
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
        return array(
            'db' => (string) $this->db,
            'type' => 'MongoDB',
        );
    }
}
