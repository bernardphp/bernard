<?php

namespace Bernard\Tests\Symfony\Command;

use Bernard\Symfony\Command\ConsumeCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ConsumeCommandTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->queues = $this->getMock('Bernard\QueueFactory');
        $this->consumer = $this->getMockBuilder('Bernard\Consumer')
            ->disableOriginalConstructor()->getMock();
    }

    public function testItConsumes()
    {
        $command = new ConsumeCommand($this->consumer, $this->queues);
        $tester = new CommandTester($command);
        $queue = $this->getMockBuilder('Bernard\Queue')->getMock();

        $this->queues->expects($this->at(0))->method('create')->with($this->equalTo('send-newsletter'))
            ->will($this->returnValue($queue));

        $this->consumer->expects($this->once())->method('consume')->with($this->equalTo($queue), null, $this->equalTo(array(
            'max-retries' => 5,
            'max-runtime' => 100,
        )));

        $tester->execute(array(
            '--max-retries' => 5,
            '--max-runtime' => 100,
            'queue' => 'send-newsletter',
        ));
    }

    public function testItConsumesWithFailed()
    {
        $command = new ConsumeCommand($this->consumer, $this->queues);
        $tester = new CommandTester($command);

        $queue = $this->getMockBuilder('Bernard\Queue')->getMock();
        $failed = $this->getMockBuilder('Bernard\Queue')->getMock();

        $this->queues->expects($this->at(0))->method('create')->with($this->equalTo('send-newsletter'))
            ->will($this->returnValue($queue));

        $this->queues->expects($this->at(1))->method('create')->with($this->equalTo('failed-send-newsletter'))
            ->will($this->returnValue($failed));

        $this->consumer->expects($this->once())->method('consume')->with($this->equalTo($queue), $this->equalTo($failed), $this->equalTo(array(
            'max-retries' => 5,
            'max-runtime' => 100,
        )));

        $tester->execute(array(
            '--max-retries' => 5,
            '--max-runtime' => 100,
            '--failed' => 'failed-send-newsletter',
            'queue' => 'send-newsletter',
        ));
    }
}
