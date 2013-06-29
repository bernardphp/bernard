<?php

namespace Bernard\Tests\Driver;

use Bernard\Doctrine\MessagesSchema;
use Bernard\Driver\DoctrineDriver;
use Doctrine\DBAL\DriverManager;

class DoctrineDriverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = $this->setUpDatabase();
        $this->driver = new DoctrineDriver($this->connection);
    }

    public function testPopMessageWithInterval()
    {
        $microtime = microtime(true);

        $this->driver->popMessage('non-existent-queue', 0.001);

        $this->assertTrue((microtime(true) - $microtime) >= 0.001);
    }

    public function testItIsAQueue()
    {
        $this->driver->pushMessage('send-newsletter', 'my-message-1');
        $this->driver->pushMessage('send-newsletter', 'my-message-2');

        // counting
        $this->assertEquals(0, $this->driver->countMessages('import-users'));
        $this->assertEquals(2, $this->driver->countMessages('send-newsletter'));

        // peeking
        $this->assertEquals(array('my-message-1', 'my-message-2'), $this->driver->peekQueue('send-newsletter'));
        $this->assertEquals(array('my-message-2'), $this->driver->peekQueue('send-newsletter', 1));
        $this->assertEquals(array('my-message-1'), $this->driver->peekQueue('send-newsletter', 0, 1));

        //var_dump($this->connection->fetchAll('select * from bernard_messages'));die;

        // popping messages
        $this->assertEquals('my-message-1', $this->driver->popMessage('send-newsletter'));
        $this->assertEquals('my-message-2', $this->driver->popMessage('send-newsletter'));

        // No messages in queue is null
        $this->assertInternalType('null', $this->driver->popMessage('import-users', 0.0001));
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

    public function testInfo()
    {
        $params = array('memory' => true, 'driver' => 'pdo_sqlite');

        $this->assertEquals($params, $this->driver->info());
    }

    protected function insertMessage($queue, $message)
    {
        $this->connection->insert('messages', compact('queue', 'message'));
    }

    protected function setUpDatabase()
    {
        $connection = DriverManager::getConnection(array(
            'memory' => true,
            'driver' => 'pdo_sqlite',
            'user' => 'henrik',
            'password' => 'notused',
        ));

        $messagesSchema = new MessagesSchema;
        $connection->getSchemaManager()->createTable($messagesSchema->createTable());

        return $connection;
    }
}
