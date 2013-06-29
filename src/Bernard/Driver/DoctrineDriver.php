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
        $statement = $this->connection->prepare('SELECT queue FROM bernard_messages GROUP BY queue');
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
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
        return $this->connection->fetchColumn('SELECT COUNT(id) FROM bernard_messages WHERE queue = :queue', array(
            'queue' => $queueName,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function pushMessage($queueName, $message)
    {
        $queue = $queueName;

        $this->connection->insert('bernard_messages', compact('queue', 'message'), array('string', 'string'));
    }

    /**
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        $this->connection->beginTransaction();

        try {
            list($id, $message) = $this->connection->fetchArray('SELECT id, message FROM bernard_messages WHERE queue = :queue', array(
                ':queue' => $queueName,
            ));

            $this->connection->delete('bernard_messages', compact('id'));

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
        $statement = $this->connection->prepare('SELECT message FROM bernard_messages LIMIT :index, :limit');
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
        $this->connection->delete('bernard_messages', array('queue' => $queueName));
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
