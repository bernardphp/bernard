<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\MongoDBDriver;

class MongoDBDriverTest extends \PHPUnit_Framework_TestCase
{
    protected $db;
    protected $driver;

    public function setUp()
    {
        $this->db = $this->setUpDatabase();
        $this->driver = new MongoDBDriver($this->db);
    }

    public function tearDown()
    {
        $this->db->dropCollection('bernardMessages');
        $this->db->dropCollection('bernardQueues');
    }

    public function testListQueues()
    {
        $this->driver->createQueue('import');
        $this->driver->createQueue('send-newsletter');

        $this->assertSimilarArrays(array('import', 'send-newsletter'), $this->driver->listQueues());
    }

    public function testPopMessage()
    {
        $this->driver->pushMessage('import', 'message1');
        $this->driver->pushMessage('import', 'message2');

        $message = $this->driver->popMessage('import');
        $this->assertNotNull($message[1], 'there should be an id');
        $this->assertInternalType('string', $message[1], 'the id should be a string');
        $this->assertSame('message1', $message[0], 'the first item pushed should be the first popped');

        $message = $this->driver->popMessage('import');
        $this->assertSame('message2', $message[0], 'the second item pushed should be popped');

        $this->assertEmpty($this->driver->popMessage('import'), 'there should be nothing left to pop');
    }

    public function testCountAndAcknowledgeMessages()
    {
        $this->assertEquals(0, $this->driver->countMessages('import-users'));

        $this->driver->pushMessage('send-newsletter', 'my-message-1');
        $this->driver->pushMessage('send-newsletter', 'my-message-2');
        $this->assertEquals(2, $this->driver->countMessages('send-newsletter'));

        list($message, $id) = $this->driver->popMessage('send-newsletter');
        $this->driver->acknowledgeMessage('send-newsletter', $id);

        $this->assertEquals(1, $this->driver->countMessages('send-newsletter'));
    }

    public function testRemoveQueue()
    {
        $this->driver->pushMessage('import', 'message1');
        $this->driver->pushMessage('import', 'message2');

        $this->assertEquals(2, $this->driver->countMessages('import'));
        $this->driver->removeQueue('import');

        $this->assertEquals(0, $this->driver->countMessages('import'));
    }

    public function testCreateAndRemoveQueue()
    {
        // Duplicates are not taking into account.
        $this->driver->createQueue('import-users');
        $this->driver->createQueue('send-newsletter');
        $this->driver->createQueue('import-users');

        $this->assertSimilarArrays(array('import-users',  'send-newsletter'), $this->driver->listQueues());

        $this->driver->removeQueue('import-users');

        $this->assertEquals(array('send-newsletter'), $this->driver->listQueues());
    }

    public function testPopMessageWithInterval()
    {
        $microtime = microtime(true);

        $this->driver->popMessage('non-existent-queue', 0.001);

        $this->assertTrue((microtime(true) - $microtime) >= 0.001);
    }

    public function testRemoveQueueRemovesMessages()
    {
        $this->driver->pushMessage('send-newsletter', 'something');
        $this->assertEquals(1, $this->driver->countMessages('send-newsletter'));

        $this->driver->removeQueue('send-newsletter');

        $this->assertEquals(0, $this->driver->countMessages('send-newsletter'));
    }

    public function testPeekQueue()
    {
        $this->driver->pushMessage('send-newsletter', 'my-message-1');
        $this->driver->pushMessage('send-newsletter', 'my-message-2');

        // peeking
        $this->assertSimilarArrays(array('my-message-1', 'my-message-2'), $this->driver->peekQueue('send-newsletter'));
        $this->assertEquals(array('my-message-2'), $this->driver->peekQueue('send-newsletter', 1));
        $this->assertEquals(array('my-message-1'), $this->driver->peekQueue('send-newsletter', 0, 1));
        $this->assertEquals(array(), $this->driver->peekQueue('import-users'));
    }

    public function testInfo()
    {
        $params = array('db' => 'bernardQueueTest', 'type' => 'MongoDB');

        $this->assertEquals($params, $this->driver->info());
    }

    protected function assertSimilarArrays($expected, $actual, $message = '')
    {
        $this->assertTrue(count($expected) == count(array_intersect($expected, $actual)), $message);
    }

    protected function setUpDatabase()
    {
        $client = new \MongoClient();

        return $client->selectDB('bernardQueueTest');
    }
}
