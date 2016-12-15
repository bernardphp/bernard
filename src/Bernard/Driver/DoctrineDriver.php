<?php

namespace Bernard\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConstraintViolationException;

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
     *
     * @throws \Exception
     */
    public function createQueue($queueName)
    {
        try {
            $this->connection->transactional(function () use ($queueName) {
                $queueExistsQb = $this->connection->createQueryBuilder();

                $queueExists = $queueExistsQb
                    ->select('name')
                    ->from('bernard_queues')
                    ->where($queueExistsQb->expr()->eq('name', ':name'))
                    ->setParameter('name', $queueName)
                    ->execute();

                if ($queueExists->fetch()) {
                    // queue was already created
                    return;
                }

                $this->connection->insert('bernard_queues', array('name' => $queueName));
            });
        } catch (ConstraintViolationException $ignored) {
            // Because SQL server does not support a portable INSERT ON IGNORE syntax
            // this ignores error based on primary key.
        }
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        return $this->connection->fetchColumn('SELECT COUNT(id) FROM bernard_messages WHERE queue = :queue AND visible = 1', array(
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

        while (microtime(true) < $runtime) {
            $this->connection->beginTransaction();

            try {
                $message = $this->doPopMessage($queueName);

                $this->connection->commit();

            } catch (\Exception $e) {
                $this->connection->rollback();
            }

            if (isset($message)) {
                return $message;
            }

            //sleep for 10 ms
            usleep(10000);
        }
    }

    protected function doPopMessage($queueName)
    {
        $query = 'SELECT id, message FROM bernard_messages
                  WHERE queue = :queue AND visible = :visible
                  ORDER BY sentAt, id LIMIT 1 ' . $this->connection->getDatabasePlatform()->getForUpdateSql();

        list($id, $message) = $this->connection->fetchArray($query, array(
            'queue' => $queueName,
            'visible' => true,
        ));

        if ($id) {
            $this->connection->update('bernard_messages', array('visible' => 0), compact('id'));

            return array($message, $id);
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
        $query = 'SELECT message FROM bernard_messages WHERE queue = ? LIMIT ' . $index . ', ' . $limit;

        $statement = $this->connection->executeQuery($query, array($queueName));

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
