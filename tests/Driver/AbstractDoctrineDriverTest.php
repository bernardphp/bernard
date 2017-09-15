<?php

namespace Bernard\Tests\Driver;

use Bernard\Doctrine\MessagesSchema;
use Bernard\Driver\DoctrineDriver;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\Schema;

abstract class AbstractDoctrineDriverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @var DoctrineDriver
     */
    protected $driver;

    public function setUp()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Doctrine have incompatibility issues with HHVM.');
        }

        if (!$this->isSupported()) {
            $this->markTestSkipped('The driver isn\'t installed on your machine');
        }

        $this->setUpDatabase();
        $this->driver = new DoctrineDriver($this->connection);
    }

    protected function tearDown()
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

    public function testPopMessageWithInterval()
    {
        $microtime = microtime(true);

        $this->driver->popMessage('non-existent-queue', 0.001);

        $this->assertTrue((microtime(true) - $microtime) >= 0.001);
    }

    public function testCreateAndRemoveQueue()
    {
        // Duplicates are not taking into account.
        $this->driver->createQueue('import-users');
        $this->driver->createQueue('send-newsletter');
        $this->driver->createQueue('import-users');

        $this->assertEquals(array('import-users',  'send-newsletter'), $this->driver->listQueues());

        $this->driver->removeQueue('import-users');

        $this->assertEquals(array('send-newsletter'), $this->driver->listQueues());
    }

    public function testPushMessageLazilyCreatesQueue()
    {
        $this->driver->pushMessage('send-newsletter', 'something');
        $this->assertEquals(array('send-newsletter'), $this->driver->listQueues());
    }

    public function testRemoveQueueRemovesMessages()
    {
        $this->driver->pushMessage('send-newsletter', 'something');
        $this->assertEquals(1, $this->driver->countMessages('send-newsletter'));

        $this->driver->removeQueue('send-newsletter');

        $this->assertEquals(0, $this->driver->countMessages('send-newsletter'));
    }

    public function testItIsAQueue()
    {
        $messages = array_map(function ($i) {
            $this->driver->pushMessage('send-newsletter', $message = 'my-message-'.$i);
            return $message;
        }, range(1, 6));

        $assertPeek = function (array $peeked, $expectedCount) use ($messages) {
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
        list($message, $id) = $this->driver->popMessage('send-newsletter');
        self::assertContains($message, $messages);
        list($message, $id) = $this->driver->popMessage('send-newsletter');
        self::assertContains($message, $messages);

        // No messages when all are invisible
        $this->assertInternalType('null', $this->driver->popMessage('import-users', 0.0001));
    }

    public function testCountMessages()
    {
        $this->assertEquals(0, $this->driver->countMessages('import-users'));

        $this->driver->pushMessage('send-newsletter', 'my-message-1');
        $this->driver->pushMessage('send-newsletter', 'my-message-2');
        $this->assertEquals(2, $this->driver->countMessages('send-newsletter'));

        list($message, $id) = $this->driver->popMessage('send-newsletter');
        $this->driver->acknowledgeMessage('send-newsletter', $id);

        $this->assertEquals(1, $this->driver->countMessages('send-newsletter'));
    }

    public function testListQueues()
    {
        $this->driver->pushMessage('import', 'message1');
        $this->driver->pushMessage('send-newsletter', 'message2');

        $this->assertEquals(array('import', 'send-newsletter'), $this->driver->listQueues());
    }

    public function testRemoveQueue()
    {
        $this->driver->pushMessage('import', 'message1');
        $this->driver->pushMessage('import', 'message2');

        $this->assertEquals(2, $this->driver->countMessages('import'));
        $this->driver->removeQueue('import');

        $this->assertEquals(0, $this->driver->countMessages('import'));
    }

    protected function insertMessage($queue, $message)
    {
        $this->connection->insert('messages', compact('queue', 'message'));
    }

    protected function setUpDatabase()
    {
        $this->connection = $this->createConnection();

        $schema = new Schema;

        MessagesSchema::create($schema);

        array_map(array($this->connection, 'executeQuery'), $schema->toSql($this->connection->getDatabasePlatform()));
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    protected abstract function createConnection();

    /**
     * @return bool
     */
    protected abstract function isSupported();
}
