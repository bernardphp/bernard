<?php

declare(strict_types=1);

namespace Bernard\Tests\Driver\Doctrine\Command;

use Bernard\Driver\Doctrine\Command\AbstractCommand;
use Symfony\Component\Console\Tester\CommandTester;

class AbstractCommandTest extends \PHPUnit\Framework\TestCase
{
    protected $command;

    protected function setUp(): void
    {
        $connection = $this->getMockBuilder('Doctrine\\DBAL\\Connection')
            ->disableOriginalConstructor()->getMock();

        $helper = $this->getMockBuilder('Doctrine\\DBAL\\Tools\\Console\\Helper\\ConnectionHelper')
            ->disableOriginalConstructor()->getMock();
        $helper
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $this->command = $this->getMockBuilder(AbstractCommand::class)
            ->setMethods(['getSql', 'applySql', 'getHelper'])
            ->setConstructorArgs(['abstract'])
            ->getMock();
        $this->command
            ->expects($this->any())
            ->method('getHelper')
            ->with('connection')
            ->willReturn($helper);
    }

    public function testExecuteWithDumpSql(): void
    {
        $this->command
            ->expects($this->once())
            ->method('getSql')
            ->with(
                $this->isInstanceOf('Doctrine\\DBAL\\Schema\\Synchronizer\\SingleDatabaseSynchronizer'),
                $this->isInstanceOf('Doctrine\\DBAL\\Schema\\Schema')
            )
            ->willReturn([]);

        $tester = new CommandTester($this->command);
        $tester->execute([
            '--dump-sql' => true,
        ]);
    }

    public function testExecuteWithoutDumpSql(): void
    {
        $this->command
            ->expects($this->once())
            ->method('applySql')
            ->with(
                $this->isInstanceOf('Doctrine\\DBAL\\Schema\\Synchronizer\\SingleDatabaseSynchronizer'),
                $this->isInstanceOf('Doctrine\\DBAL\\Schema\\Schema')
            );

        $tester = new CommandTester($this->command);
        $tester->execute([]);
    }
}
