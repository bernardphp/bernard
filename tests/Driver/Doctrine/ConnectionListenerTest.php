<?php

declare(strict_types=1);

namespace Bernard\Tests\Driver\Doctrine;

use Bernard\Driver\Doctrine\ConnectionListener;
use Doctrine\DBAL\DBALException;

class ConnectionListenerTest extends \PHPUnit\Framework\TestCase
{
    use \Prophecy\PhpUnit\ProphecyTrait;

    protected function setUp(): void
    {
        $this->connection = $this->prophesize('Doctrine\DBAL\Connection');

        $this->listener = new ConnectionListener($this->connection->reveal());
    }

    public function testPing(): void
    {
        $this->connection->isConnected()->willReturn(true);

        $platform = $this->prophesize('Doctrine\DBAL\Platforms\SqlitePlatform');
        $platform->getDummySelectSQL()->willReturn('SELECT 1');

        $this->connection->getDatabasePlatform()->willReturn($platform->reveal());
        $this->connection->query('SELECT 1')->shouldBeCalled();

        $this->listener->onPing();
    }

    public function testPingOnNotConnectedConnection(): void
    {
        $this->connection->isConnected()->willReturn(false);

        $this->listener->onPing();
    }

    public function testCloseConnectionIfPingFails(): void
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
