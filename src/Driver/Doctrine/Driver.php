<?php

declare(strict_types=1);

namespace Bernard\Driver\Doctrine;

use Bernard\Driver\Message;
use Doctrine\DBAL\Connection;

final class Driver implements \Bernard\Driver
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function listQueues(): array
    {
        $statement = $this->connection->prepare('SELECT name FROM bernard_queues');
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function createQueue(string $queueName): void
    {
        try {
            $this->connection->insert('bernard_queues', ['name' => $queueName]);
        } catch (\Exception $e) {
            // Because SQL server does not support a portable INSERT ON IGNORE syntax
            // this ignores error based on primary key.
        }
    }

    public function removeQueue(string $queueName): void
    {
        $this->connection->delete('bernard_messages', ['queue' => $queueName]);
        $this->connection->delete('bernard_queues', ['name' => $queueName]);
    }

    public function pushMessage(string $queueName, string $message): void
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

    public function popMessage(string $queueName, int $duration = 5): ?Message
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

            // sleep for 10 ms
            usleep(10000);
        }

        return null;
    }

    /**
     * Execute the actual query and process the response.
     */
    private function doPopMessage(string $queueName): ?Message
    {
        $query = 'SELECT id, message FROM bernard_messages
                  WHERE queue = :queue AND visible = :visible
                  ORDER BY sentAt LIMIT 1 '.$this->connection->getDatabasePlatform()->getForUpdateSql();

        [$id, $message] = $this->connection->fetchArray($query, [
            'queue' => $queueName,
            'visible' => true,
        ]);

        if ($id) {
            $this->connection->update('bernard_messages', ['visible' => 0], compact('id'));

            return new Message($message, $id);
        }
    }

    public function acknowledgeMessage(string $queueName, mixed $receipt): void
    {
        $this->connection->delete('bernard_messages', ['id' => $receipt, 'queue' => $queueName]);
    }

    public function info(): array
    {
        $params = $this->connection->getParams();

        unset($params['user'], $params['password']);

        return $params;
    }

    public function countMessages(string $queueName): int
    {
        $query = 'SELECT COUNT(id) FROM bernard_messages WHERE queue = :queue AND visible = :visible';

        return (int) $this->connection->fetchColumn($query, [
            'queue' => $queueName,
            'visible' => true,
        ]);
    }

    public function peekQueue(string $queueName, int $index = 0, int $limit = 20): array
    {
        $parameters = [$queueName, true, $limit, $index];
        $types = ['string', 'boolean', 'integer', 'integer'];

        $query = 'SELECT message FROM bernard_messages WHERE queue = ? AND visible = ? ORDER BY sentAt LIMIT ? OFFSET ?';

        return $this
            ->connection
            ->executeQuery($query, $parameters, $types)
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
