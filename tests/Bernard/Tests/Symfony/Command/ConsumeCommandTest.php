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
        $queue = $this->getMockBuilder('Bernard\Queue')->disableOriginalConstructor()->getMock();
        $failed = $this->getMockBuilder('Bernard\Queue')->disableOriginalConstructor()->getMock();

        $this->queues->expects($this->at(0))->method('create')->with($this->equalTo('send-newsletter'))
            ->will($this->returnValue($queue));

        $this->queues->expects($this->at(1))->method('create')->with($this->equalTo('failed'))
            ->will($this->returnValue($failed));

        $this->consumer->expects($this->once())->method('consume')->with($this->equalTo($queue), $this->equalTo($failed), $this->equalTo(array(
            'max-retries' => 5,
            'max-runtime' => 100,
        )));

        $command = new ConsumeCommand($this->consumer, $this->queues);

        $tester = new CommandTester($command);
        $tester->execute(array(
            '--max-retries' => 5,
            '--max-runtime' => 100,
            'queue' => 'send-newsletter',
        ));
    }
}
