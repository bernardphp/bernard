<?php

namespace Bernard\Tests\EventListener\Doctrine;

use Bernard\EventListener\Doctrine\PingEventListener;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\DBALException;

class PingEventListenerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->connection = $this->prophesize(Connection::class);

        $this->listener = new PingEventListener($this->connection->reveal());
    }

    public function testPing()
    {
        $this->connection->isConnected()->willReturn(true);

        $platform = $this->prophesize(SqlitePlatform::class);
        $platform->getDummySelectSQL()->willReturn('SELECT 1');

        $this->connection->getDatabasePlatform()->willReturn($platform->reveal());
        $this->connection->query('SELECT 1')->shouldBeCalled();

        $this->listener->onPing();
    }

    public function testPingOnNotConnectedConnection()
    {
        $this->connection->isConnected()->willReturn(false);

        $this->listener->onPing();
    }

    public function testCloseConnectionIfPingFails()
    {
        $this->connection->isConnected()->willReturn(true);
        $this->connection->query('SELECT 1')->willThrow(new DBALException());
        $this->connection->close()->shouldBeCalled();

        $platform = $this->prophesize(SqlitePlatform::class);
        $platform->getDummySelectSQL()->willReturn('SELECT 1');

        $this->connection->getDatabasePlatform()->willReturn($platform->reveal());
        $this->connection->query('SELECT 1')->shouldBeCalled();

        $this->listener->onPing();
    }
}
