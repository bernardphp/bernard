<?php

namespace Bernard\Driver;

use Doctrine\DBAL\Connection;

/**
 * Driver supporting Doctrine DBAL
 *
 * @package Bernard
 */
class DoctrineDriver implements \Bernard\Driver
{
    protected $connection;

    /**
     * {@inheritDoc}
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function listQueues()
    {
        $statement = $this->connection->prepare('SELECT name FROM bernard_queues');
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * {@inheritDoc}
     */
    public function createQueue($queueName)
    {
        try {
            $this->connection->insert('bernard_queues', array('name' => $queueName));
        } catch (\Exception $e) {}
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        return $this->connection->fetchColumn('SELECT COUNT(id) FROM bernard_messages WHERE queue = :queue', array(
            'queue' => $queueName,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function pushMessage($queueName, $message)
    {
        $types = array('string', 'string', 'datetime');
        $data = array(
            'queue'   => $queueName,
            'message' => $message,
            'sentAt'  => new \DateTime(),
        );

        $this->createQueue($queueName);
        $this->connection->insert('bernard_messages', $data, $types);
    }

    /**
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        $runtime = microtime(true) + $interval;
        $query = 'SELECT id, message FROM bernard_messages
                  WHERE queue = :queue AND visible = :visible
                  ORDER BY sentAt, id ' . $this->connection->getDatabasePlatform()->getForUpdateSql();

        while (microtime(true) < $runtime) {
            $this->connection->beginTransaction();

            try {
                list($id, $message) = $this->connection->fetchArray($query, array('queue' => $queueName, 'visible' => true));

                $this->connection->update('bernard_messages', array('visible' => false), compact('id'));
                $this->connection->commit();
            } catch (\Exception $e) {
                $this->connection->rollback();
            }

            if (isset($message) && $message) {
                return array($message, $id);
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
        $this->connection->delete('bernard_messages', array('id' => $receipt, 'queue' => $queueName));
    }

    /**
     * {@inheritDoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        $query = 'SELECT message FROM bernard_messages WHERE queue = ? LIMIT ?, ?';
        $params = array(
            $queueName,
            $index,
            $limit,
        );

        $statement = $this->connection->executeQuery($query, $params, array(
            'string', 'integer', 'integer'
        ));

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * {@inheritDoc}
     */
    public function removeQueue($queueName)
    {
        $this->connection->delete('bernard_messages', array('queue' => $queueName));
        $this->connection->delete('bernard_queues', array('name' => $queueName));
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
        $params = $this->connection->getParams();

        unset($params['user'], $params['password']);

        return $params;
    }
}
