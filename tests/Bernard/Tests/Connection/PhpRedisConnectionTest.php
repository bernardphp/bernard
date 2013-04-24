<?php

namespace Bernard\Tests\Connection;

use Bernard\Connection\PhpRedisConnection;

class PhpRedisConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->redis = $this->getMock('Redis', array(
            'lLen',
            'sMembers',
            'lRange',
            'blPop',
            'sRemove',
            'delete',
            'sAdd',
            'sContains',
            'rPush',
        ));

        $this->connection = new PhpRedisConnection($this->redis);
    }

    public function testItImplementsConnectionInterface()
    {
        $this->assertInstanceOf('Bernard\Connection', $this->connection);
    }

    public function testItCountsSetLength()
    {
        $this->redis->expects($this->once())->method('lLen')->with($this->equalTo('queues'))
            ->will($this->returnValue(4));

        $this->assertEquals(4, $this->connection->count('queues'));
    }

    public function testItGetsAllKeys()
    {
        $this->redis->expects($this->once())->method('sMembers')->with($this->equalTo('queues'))
            ->will($this->returnValue(array('failed', 'queue1')));

        $this->assertEquals(array('failed', 'queue1'), $this->connection->all('queues'));
    }

    public function testItSlicesASet()
    {
        $this->redis->expects($this->at(0))->method('lRange')
            ->with($this->equalTo('queues'), $this->equalTo(10), $this->equalTo(19))
            ->will($this->returnValue(array('message1')));

        $this->redis->expects($this->at(1))->method('lRange')
            ->with($this->equalTo('queues'), $this->equalTo(0), $this->equalTo(19))
            ->will($this->returnValue(array('message2')));

        $this->assertEquals(array('message1'), $this->connection->slice('queues', 10, 10));
        $this->assertEquals(array('message2'), $this->connection->slice('queues'));

    }

    public function testItRemovesAKeyFromASet()
    {
        $this->redis->expects($this->once())->method('sRemove')->with($this->equalTo('queues'), $this->equalTo('queue'));

        $this->connection->remove('queues', 'queue');
    }

    public function testItDeletesASet()
    {
        $this->redis->expects($this->once())->method('delete')->with($this->equalTo('queue:name'));
        $this->connection->delete('queue:name');
    }

    public function testItInsertsToSet()
    {
        $this->redis->expects($this->once())->method('sAdd')->with($this->equalTo('queues'), $this->equalTo('queue'));

        $this->connection->insert('queues', 'queue');
    }

    public function testItChecksSetForMemeber()
    {
        $this->redis->expects($this->at(0))->method('sContains')->with($this->equalTo('queues'), $this->equalTo('queue:name'))
            ->will($this->returnValue(true));

        $this->redis->expects($this->at(1))->method('sContains')->with($this->equalTo('queues'), $this->equalTo('queue:name-2'))
            ->will($this->returnValue(false));

        $this->assertTrue($this->connection->contains('queues', 'queue:name'));
        $this->assertFalse($this->connection->contains('queues', 'queue:name-2'));
    }

    public function testItPushesMember()
    {
        $this->redis->expects($this->once())->method('rPush')->with($this->equalTo('queues'), $this->equalTo('my-queue'));

        $this->connection->push('queues', 'my-queue');
    }

    public function testItPopMessages()
    {
        $this->redis->expects($this->at(0))->method('blPop')->with($this->equalTo('queues'))
            ->will($this->returnValue(array('my-queue', 'message1')));

        $this->redis->expects($this->at(1))->method('blPop')->with($this->equalTo('queues2'), $this->equalTo(30))
            ->will($this->returnValue(array('my-queue2', 'message2')));

        $this->assertEquals('message1', $this->connection->pop('queues'));
        $this->assertEquals('message2', $this->connection->pop('queues2', 30));
    }
}
