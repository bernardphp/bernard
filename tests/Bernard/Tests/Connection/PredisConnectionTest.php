<?php

namespace Bernard\Tests\Connection;

use Bernard\Connection\PredisConnection;

class PredisConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Because predis uses __call all methods that needs mocking must be
        // explicitly defined.
        $this->predis = $this->getMock('Predis\Client', array(
            'llen',
            'smembers',
            'lrange',
            'blpop',
            'srem',
            'del',
            'sadd',
            'sismember',
            'rpush',
        ));

        $this->connection = new PredisConnection($this->predis);
    }

    public function testItImplementsConnectionInterface()
    {
        $this->assertInstanceOf('Bernard\Connection', $this->connection);
    }

    public function testItCountsSetLength()
    {
        $this->predis->expects($this->once())->method('llen')->with($this->equalTo('queues'))
            ->will($this->returnValue(4));

        $this->assertEquals(4, $this->connection->count('queues'));
    }

    public function testItGetsAllKeys()
    {
        $this->predis->expects($this->once())->method('smembers')->with($this->equalTo('queues'))
            ->will($this->returnValue(array('failed', 'queue1')));

        $this->assertEquals(array('failed', 'queue1'), $this->connection->all('queues'));
    }

    public function testItSlicesASet()
    {
        $this->predis->expects($this->at(0))->method('lrange')
            ->with($this->equalTo('queues'), $this->equalTo(10), $this->equalTo(19))
            ->will($this->returnValue(array('message1')));

        $this->predis->expects($this->at(1))->method('lrange')
            ->with($this->equalTo('queues'), $this->equalTo(0), $this->equalTo(19))
            ->will($this->returnValue(array('message2')));

        $this->assertEquals(array('message1'), $this->connection->slice('queues', 10, 10));
        $this->assertEquals(array('message2'), $this->connection->slice('queues'));

    }

    public function testItRemovesAKeyFromASet()
    {
        $this->predis->expects($this->once())->method('srem')->with($this->equalTo('queues'), $this->equalTo('queue'));

        $this->connection->remove('queues', 'queue');
    }

    public function testItDeletesASet()
    {
        $this->predis->expects($this->once())->method('del')->with($this->equalTo('queue:name'));
        $this->connection->delete('queue:name');
    }

    public function testItInsertsToSet()
    {
        $this->predis->expects($this->once())->method('sadd')->with($this->equalTo('queues'), $this->equalTo('queue'));

        $this->connection->insert('queues', 'queue');
    }

    public function testItChecksSetForMemeber()
    {
        $this->predis->expects($this->at(0))->method('sismember')->with($this->equalTo('queues'), $this->equalTo('queue:name'))
            ->will($this->returnValue(true));

        $this->predis->expects($this->at(1))->method('sismember')->with($this->equalTo('queues'), $this->equalTo('queue:name-2'))
            ->will($this->returnValue(false));

        $this->assertTrue($this->connection->contains('queues', 'queue:name'));
        $this->assertFalse($this->connection->contains('queues', 'queue:name-2'));
    }

    public function testItPushesMember()
    {
        $this->predis->expects($this->once())->method('rpush')->with($this->equalTo('queues'), $this->equalTo('my-queue'));

        $this->connection->push('queues', 'my-queue');
    }

    public function testItPopMessages()
    {
        $this->predis->expects($this->at(0))->method('blpop')->with($this->equalTo('queues'))
            ->will($this->returnValue(array('my-queue', 'message1')));

        $this->predis->expects($this->at(1))->method('blpop')->with($this->equalTo('queues2'), $this->equalTo(30))
            ->will($this->returnValue(array('my-queue2', 'message2')));

        $this->assertEquals('message1', $this->connection->pop('queues'));
        $this->assertEquals('message2', $this->connection->pop('queues2', 30));
    }

    public function testItCanUseAnInterface()
    {
        $connection = new PredisConnection($predis = $this->getMock('Predis\ClientInterface'));

        $this->assertSame($predis, $connection->getClient());
    }
}
