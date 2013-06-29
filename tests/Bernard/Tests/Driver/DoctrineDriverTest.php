<?php

namespace Bernard\Tests\Driver;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Bernard\Driver\DoctrineDriver;

class DoctrineDriverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = $this->setUpDatabase();
        $this->driver = new DoctrineDriver($this->connection);
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

        // popping messages
        $this->assertEquals('my-message-1', $this->driver->popMessage('send-newsletter'));
        $this->assertEquals('my-message-2', $this->driver->popMessage('send-newsletter'));

        // No messages in queue is null
        $this->assertInternalType('null', $this->driver->popMessage('import-users'));
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
        $table = new Table('messages', array(
            new Column('id', Type::getType('integer'), array(
                'notnull'       => true,
                'autoincrement' => true,
                'unsigned'      => true,
            )),
            new Column('queue', Type::getType('string')),
            new Column('message', Type::getType('text')),
        ));
        $table->setPrimaryKey(array('id'), 'id_x');

        $connection = DriverManager::getConnection(array(
            'memory' => true,
            'driver' => 'pdo_sqlite',
            'user' => 'henrik',
            'password' => 'notused',
        ));
        $connection->getSchemaManager()->createTable($table);

        return $connection;
    }
}