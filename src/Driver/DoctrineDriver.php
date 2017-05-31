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
     * {@inheritdoc}
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function listQueues()
    {
        $statement = $this->connection->prepare('SELECT name FROM bernard_queues');
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Return true if the $queueName has been already inserted on the database
     *
     * @param $queueName
     * @return bool
     */
    private function isQueuePersisted($queueName)
    {
        $query = 'SELECT COUNT(name) FROM bernard_queues WHERE name = :name';

        return $this->connection->fetchColumn($query, [
            'name' => $queueName,
        ]) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue($queueName)
    {
        if (!$this->isQueuePersisted($queueName)) {
            $this->connection->insert('bernard_queues', ['name' => $queueName]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countMessages($queueName)
    {
        $query = 'SELECT COUNT(id) FROM bernard_messages WHERE queue = :queue AND visible = :visible';

        return (integer) $this->connection->fetchColumn($query, [
            'queue' => $queueName,
            'visible' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function pushMessage($queueName, $message)
    {
        $types = ['string', 'string', 'datetime'];
        $data = [
            'queue' => $queueName,
            'message' => $message,
            'sentAt' => new \DateTime(),
        ];

        $this->createQueue($queueName);
        $this->connection->insert('bernard_messages', $data, $types);
    }

    /**
     * {@inheritdoc}
     */
    public function popMessage($queueName, $duration = 5)
    {
        $runtime = microtime(true) + $duration;

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

    /**
     * {@inheritdoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $this->connection->delete('bernard_messages', ['id' => $receipt, 'queue' => $queueName]);
    }

    /**
     * {@inheritdoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        $parameters = [$queueName, $limit, $index];
        $types = ['string', 'integer', 'integer'];

        $query = 'SELECT message FROM bernard_messages WHERE queue = ? ORDER BY sentAt LIMIT ? OFFSET ?';

        return $this
            ->connection
            ->executeQuery($query, $parameters, $types)
            ->fetchAll(\PDO::FETCH_COLUMN)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function removeQueue($queueName)
    {
        $this->connection->delete('bernard_messages', ['queue' => $queueName]);
        $this->connection->delete('bernard_queues', ['name' => $queueName]);
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        $params = $this->connection->getParams();

        unset($params['user'], $params['password']);

        return $params;
    }

    /**
     * Execute the actual query and process the response
     *
     * @param string $queueName
     *
     * @return array|null
     */
    protected function doPopMessage($queueName)
    {
        $query = 'SELECT id, message FROM bernard_messages
                  WHERE queue = :queue AND visible = :visible
                  ORDER BY sentAt LIMIT 1 ' . $this->connection->getDatabasePlatform()->getForUpdateSql();

        list($id, $message) = $this->connection->fetchArray($query, [
            'queue' => $queueName,
            'visible' => true,
        ]);

        if ($id) {
            $this->connection->update('bernard_messages', ['visible' => 0], compact('id'));

            return [$message, $id];
        }
    }
}
