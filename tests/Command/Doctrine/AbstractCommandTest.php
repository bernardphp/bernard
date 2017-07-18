<?php

namespace Bernard\Tests\Command\Doctrine;

use Bernard\Command\Doctrine\AbstractCommand;
use Symfony\Component\Console\Tester\CommandTester;

class AbstractCommandTest extends \PHPUnit\Framework\TestCase
{
    protected $command;

    public function setUp()
    {
        $connection = $this->getMockBuilder('Doctrine\\DBAL\\Connection')
            ->disableOriginalConstructor()->getMock();

        $helper = $this->getMockBuilder('Doctrine\\DBAL\\Tools\\Console\\Helper\\ConnectionHelper')
            ->disableOriginalConstructor()->getMock();
        $helper
            ->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $this->command = $this->getMockBuilder('Bernard\\Command\\Doctrine\\AbstractCommand')
            ->setMethods(['getSql', 'applySql', 'getHelper'])
            ->setConstructorArgs(['abstract'])
            ->getMock();
        $this->command
            ->expects($this->any())
            ->method('getHelper')
            ->with('connection')
            ->will($this->returnValue($helper));
    }

    public function testExecuteWithDumpSql()
    {
        $this->command
            ->expects($this->once())
            ->method('getSql')
            ->with(
                $this->isInstanceOf('Doctrine\\DBAL\\Schema\\Synchronizer\\SingleDatabaseSynchronizer'),
                $this->isInstanceOf('Doctrine\\DBAL\\Schema\\Schema')
            )
            ->will($this->returnValue([]));

        $tester = new CommandTester($this->command);
        $tester->execute([
            '--dump-sql' => true,
        ]);
    }

    public function testExecuteWithoutDumpSql()
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
