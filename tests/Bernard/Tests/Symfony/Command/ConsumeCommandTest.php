<?php

namespace Bernard\Tests\Symfony\Command;

use Bernard\Symfony\Command\ConsumeCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ConsumeCommandTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->queues = $this->getMock('Bernard\QueueFactory');
        $this->services = $this->getMock('Bernard\ServiceResolver');
    }

    public function testItCanCreateAConsumer()
    {
        $command = new ConsumeCommand($this->services, $this->queues);
        $this->assertInstanceOf('Bernard\ConsumerInterface', $command->getConsumer());
    }

    public function testItConsumes()
    {
        $queue = $this->getMockBuilder('Bernard\Queue')->disableOriginalConstructor()->getMock();
        $failed = $this->getMockBuilder('Bernard\Queue')->disableOriginalConstructor()->getMock();

        $this->queues->expects($this->at(0))->method('create')->with($this->equalTo('send-newsletter'))
            ->will($this->returnValue($queue));

        $this->queues->expects($this->at(1))->method('create')->with($this->equalTo('failed'))
            ->will($this->returnValue($failed));

        $consumer = $this->getMock('Bernard\ConsumerInterface');
        $consumer->expects($this->once())->method('consume')->with($this->equalTo($queue), $this->equalTo($failed), $this->equalTo(array(
            'max-retries' => 5,
            'max-runtime' => 100,
        )));

        $command = $this->getMockBuilder('Bernard\Symfony\Command\ConsumeCommand')
            ->setMethods(array('getConsumer'))
            ->setConstructorArgs(array($this->services, $this->queues))->getMock();

        $command->expects($this->once())->method('getConsumer')->will($this->returnValue($consumer));

        $tester = new CommandTester($command);
        $tester->execute(array(
            '--max-retries' => 5,
            '--max-runtime' => 100,
            'queue' => 'send-newsletter',
        ));
    }
}
