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
        return $this->connection->fetchColumn('SELECT queue FROM messages GROUP BY queue');
    }

    /**
     * {@inheritDoc}
     */
    public function createQueue($queueName)
    {
        // noop
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        $sql = 'SELECT COUNT(id) FROM messages WHERE queue = :queue';

        return $this->connection->fetchColumn($sql, array(
            'queue' => $queueName,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function pushMessage($queueName, $message)
    {
        $queue = $queueName;

        $this->connection->insert('messages', compact('queue', 'message'), array(
            'string',
            'string',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        $this->connection->beginTransaction();

        try {
            list($id, $message) = $this->connection->fetchArray('SELECT id, message FROM messages WHERE queue = :queue', array(
                ':queue' => $queueName,
            ));

            $this->connection->delete('messages', compact('id'));

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollback();

            throw $e;
        }

        if (isset($message)) {
            return $message;
        }

        // Sleep 100 ms between each select.
        usleep(100);
    }

    /**
     * {@inheritDoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        $statement = $this->connection->prepare('SELECT message FROM messages LIMIT :index, :limit');
        $statement->execute(array(
            ':index' => $index,
            ':limit' => $index + $limit,
        ));

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * {@inheritDoc}
     */
    public function removeQueue($queueName)
    {
        $this->connection->delete('messages', array('queue' => $queueName));
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
