<?php

declare(strict_types=1);

namespace Bernard\Tests\Driver\Doctrine;

use Bernard\Driver\Doctrine\Driver;
use Bernard\Driver\Doctrine\MessagesSchema;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\Schema;

abstract class AbstractDriverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @var Driver
     */
    protected $driver;

    protected function setUp(): void
    {
        if (\defined('HHVM_VERSION')) {
            $this->markTestSkipped('Doctrine have incompatibility issues with HHVM.');
        }

        if (!$this->isSupported()) {
            $this->markTestSkipped('The driver isn\'t installed on your machine');
        }

        $this->setUpDatabase();
        $this->driver = new Driver($this->connection);
    }

    protected function tearDown(): void
    {
        if ($this->connection->getDatabasePlatform() instanceof MySqlPlatform) {
            $this->connection->exec('SET FOREIGN_KEY_CHECKS = 0');
        }

        foreach ($this->connection->getSchemaManager()->listTables() as $table) {
            $sql = $this->connection->getDatabasePlatform()->getDropTableSQL($table->getName());
            $this->connection->exec($sql);
        }

        if ($this->connection->getDatabasePlatform() instanceof MySqlPlatform) {
            $this->connection->exec('SET FOREIGN_KEY_CHECKS = 0');
        }
    }

    public function testPopMessageWithInterval(): void
    {
        $microtime = microtime(true);

        $this->driver->popMessage('non-existent-queue', 0.001);

        $this->assertGreaterThanOrEqual(0.001, microtime(true) - $microtime);
    }

    public function testCreateAndRemoveQueue(): void
    {
        // Duplicates are not taking into account.
        $this->driver->createQueue('import-users');
        $this->driver->createQueue('send-newsletter');
        $this->driver->createQueue('import-users');

        $this->assertEquals(['import-users',  'send-newsletter'], $this->driver->listQueues());

        $this->driver->removeQueue('import-users');

        $this->assertEquals(['send-newsletter'], $this->driver->listQueues());
    }

    public function testPushMessageLazilyCreatesQueue(): void
    {
        $this->driver->pushMessage('send-newsletter', 'something');
        $this->assertEquals(['send-newsletter'], $this->driver->listQueues());
    }

    public function testRemoveQueueRemovesMessages(): void
    {
        $this->driver->pushMessage('send-newsletter', 'something');
        $this->assertEquals(1, $this->driver->countMessages('send-newsletter'));

        $this->driver->removeQueue('send-newsletter');

        $this->assertEquals(0, $this->driver->countMessages('send-newsletter'));
    }

    public function testItIsAQueue(): void
    {
        $messages = array_map(function ($i) {
            $this->driver->pushMessage('send-newsletter', $message = 'my-message-'.$i);

            return $message;
        }, range(1, 6));

        $assertPeek = function (array $peeked, $expectedCount) use ($messages): void {
            self::assertCount($expectedCount, $peeked);
            foreach ($peeked as $peek) {
                self::assertContains($peek, $messages);
            }
        };

        // peeking
        $assertPeek($this->driver->peekQueue('send-newsletter'), 6);
        $assertPeek($this->driver->peekQueue('send-newsletter', 0, 3), 3);
        $assertPeek($this->driver->peekQueue('send-newsletter', 1), 5);

        $this->assertEquals([], $this->driver->peekQueue('import-users'));

        // popping
        [$message, $id] = $this->driver->popMessage('send-newsletter');
        self::assertContains($message, $messages);
        [$message, $id] = $this->driver->popMessage('send-newsletter');
        self::assertContains($message, $messages);

        // No messages when all are invisible
        $this->assertNull($this->driver->popMessage('import-users', 0.0001));
    }

    public function testCountMessages(): void
    {
        $this->assertEquals(0, $this->driver->countMessages('import-users'));

        $this->driver->pushMessage('send-newsletter', 'my-message-1');
        $this->driver->pushMessage('send-newsletter', 'my-message-2');
        $this->assertEquals(2, $this->driver->countMessages('send-newsletter'));

        [$message, $id] = $this->driver->popMessage('send-newsletter');
        $this->driver->acknowledgeMessage('send-newsletter', $id);

        $this->assertEquals(1, $this->driver->countMessages('send-newsletter'));
    }

    public function testPeekMessagesExcludesPoppedMessages(): void
    {
        $this->driver->pushMessage('send-newsletter', 'my-message-1');
        $this->driver->pushMessage('send-newsletter', 'my-message-2');
        $this->driver->pushMessage('send-newsletter', 'my-message-3');

        $this->assertCount(3, $this->driver->peekQueue('send-newsletter'));
        $this->assertEquals(3, $this->driver->countMessages('send-newsletter'));

        $this->driver->popMessage('send-newsletter');

        $this->assertCount(2, $this->driver->peekQueue('send-newsletter'));
        $this->assertEquals(2, $this->driver->countMessages('send-newsletter'));
    }

    public function testListQueues(): void
    {
        $this->driver->pushMessage('import', 'message1');
        $this->driver->pushMessage('send-newsletter', 'message2');

        $this->assertEquals(['import', 'send-newsletter'], $this->driver->listQueues());
    }

    public function testRemoveQueue(): void
    {
        $this->driver->pushMessage('import', 'message1');
        $this->driver->pushMessage('import', 'message2');

        $this->assertEquals(2, $this->driver->countMessages('import'));
        $this->driver->removeQueue('import');

        $this->assertEquals(0, $this->driver->countMessages('import'));
    }

    protected function insertMessage($queue, $message): void
    {
        $this->connection->insert('messages', compact('queue', 'message'));
    }

    protected function setUpDatabase(): void
    {
        $this->connection = $this->createConnection();

        $schema = new Schema();

        MessagesSchema::create($schema);

        array_map([$this->connection, 'executeQuery'], $schema->toSql($this->connection->getDatabasePlatform()));
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    abstract protected function createConnection();

    /**
     * @return bool
     */
    abstract protected function isSupported();
}
