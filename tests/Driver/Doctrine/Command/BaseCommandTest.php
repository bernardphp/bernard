<?php

declare(strict_types=1);

namespace Bernard\Tests\Driver\Doctrine\Command;

use Symfony\Component\Console\Tester\CommandTester;

abstract class BaseCommandTest extends \PHPUnit\Framework\TestCase
{
    protected $command;

    protected $sync;

    protected function setUp(): void
    {
        $connection = $this->getMockBuilder('Doctrine\\DBAL\\Connection')
            ->disableOriginalConstructor()->getMock();

        $this->sync = $this->getMockBuilder('Doctrine\\DBAL\\Schema\\Synchronizer\\SingleDatabaseSynchronizer')
            ->disableOriginalConstructor()->getMock();

        $helper = $this->getMockBuilder('Doctrine\\DBAL\\Tools\\Console\\Helper\\ConnectionHelper')
            ->disableOriginalConstructor()->getMock();
        $helper
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $this->command = $this->getMockBuilder('Bernard\\Driver\\Doctrine\\Command\\'.$this->getShortClassName())
            ->setMethods(['getSynchronizer', 'getHelper'])
            ->setConstructorArgs([$connection])
            ->getMock();
        $this->command
            ->expects($this->any())
            ->method('getSynchronizer')
            ->with($connection)
            ->willReturn($this->sync);
        $this->command
            ->expects($this->any())
            ->method('getHelper')
            ->with('connection')
            ->willReturn($helper);
    }

    public function testExecuteWithDumpSql(): void
    {
        $this->sync
            ->expects($this->once())
            ->method($this->getSqlMethod())
            ->with($this->isInstanceOf('Doctrine\\DBAL\\Schema\\Schema'))
            ->willReturn([]);

        $tester = new CommandTester($this->command);
        $tester->execute([
            '--dump-sql' => true,
        ]);
    }

    public function testExecuteWithoutDumpSql(): void
    {
        $this->sync
            ->expects($this->once())
            ->method($this->applySqlMethod())
            ->with($this->isInstanceOf('Doctrine\\DBAL\\Schema\\Schema'));

        $tester = new CommandTester($this->command);
        $tester->execute([]);
    }

    /**
     * @return string
     */
    abstract public function getShortClassName();

    /**
     * @return string
     */
    abstract public function getSqlMethod();

    /**
     * @return string
     */
    abstract public function applySqlMethod();
}
