<?php

namespace Raekke\Tests;

use Raekke\Connection;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->predis = $this->getMock('Predis\Client', array(
            'llen',
            'smembers',
            'lrange',
            'blpop',
        ));

        $this->connection = new Connection($this->predis);
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

    public function testItCanUseAnInterface()
    {
        $connection = new Connection($predis = $this->getMock('Predis\ClientInterface'));

        $this->assertSame($predis, $connection->getClient());
    }
}
