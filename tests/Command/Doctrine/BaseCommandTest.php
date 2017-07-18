<?php

namespace Bernard\Tests\Command\Doctrine;

use Symfony\Component\Console\Tester\CommandTester;

abstract class BaseCommandTest extends \PHPUnit\Framework\TestCase
{
    protected $command;

    protected $sync;

    public function setUp()
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
            ->will($this->returnValue($connection));

        $this->command = $this->getMockBuilder('Bernard\\Command\\Doctrine\\' . $this->getShortClassName())
            ->setMethods(['getSynchronizer', 'getHelper'])
            ->setConstructorArgs([$connection])
            ->getMock();
        $this->command
            ->expects($this->any())
            ->method('getSynchronizer')
            ->with($connection)
            ->will($this->returnValue($this->sync));
        $this->command
            ->expects($this->any())
            ->method('getHelper')
            ->with('connection')
            ->will($this->returnValue($helper));
    }

    public function testExecuteWithDumpSql()
    {
        $this->sync
            ->expects($this->once())
            ->method($this->getSqlMethod())
            ->with($this->isInstanceOf('Doctrine\\DBAL\\Schema\\Schema'))
            ->will($this->returnValue([]));

        $tester = new CommandTester($this->command);
        $tester->execute([
            '--dump-sql' => true,
        ]);
    }

    public function testExecuteWithoutDumpSql()
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
