<?php

namespace Bernard\Tests\Doctrine;

use Bernard\Doctrine\ConnectionListener;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\DBALException;

class ConnectionListenerTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        $this->connection = $this->prophesize('Doctrine\DBAL\Connection');

        $this->listener = new ConnectionListener($this->connection->reveal());
    }

    public function testPing()
    {
        $this->connection->isConnected()->willReturn(true);

        $platform = $this->prophesize('Doctrine\DBAL\Platforms\SqlitePlatform');
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

        $platform = $this->prophesize('Doctrine\DBAL\Platforms\SqlitePlatform');
        $platform->getDummySelectSQL()->willReturn('SELECT 1');

        $this->connection->getDatabasePlatform()->willReturn($platform->reveal());
        $this->connection->query('SELECT 1')->shouldBeCalled();

        $this->listener->onPing();
    }
}
