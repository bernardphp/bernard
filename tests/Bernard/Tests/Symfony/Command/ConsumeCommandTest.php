<?php

namespace Bernard\Tests\Symfony\Command;

use Bernard\Symfony\Command\ConsumeCommand;
use Bernard\QueueFactory\InMemoryFactory;
use Symfony\Component\Console\Tester\CommandTester;

class ConsumeCommandTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->queues = new InMemoryFactory;
        $this->consumer = $this->getMockBuilder('Bernard\Consumer')
            ->disableOriginalConstructor()->getMock();
    }

    public function testItConsumes()
    {
        $command = new ConsumeCommand($this->consumer, $this->queues);
        $queue = $this->queues->create('send-newsletter');

        $this->consumer->expects($this->once())->method('consume')->with($this->equalTo($queue), null, $this->equalTo(array(
            'max-retries' => 5,
            'max-runtime' => 100,
        )));

        $tester = new CommandTester($command);
        $tester->execute(array(
            '--max-retries' => 5,
            '--max-runtime' => 100,
            'queue' => 'send-newsletter',
        ));
    }

    public function testItConsumesWithFailed()
    {
        $command = new ConsumeCommand($this->consumer, $this->queues);

        $queue = $this->queues->create('send-newsletter');
        $failed = $this->queues->create('failed-send-newsletter');

        $this->consumer->expects($this->once())->method('consume')->with($this->equalTo($queue), $this->equalTo($failed), $this->equalTo(array(
            'max-retries' => 5,
            'max-runtime' => 100,
        )));

        $tester = new CommandTester($command);
        $tester->execute(array(
            '--max-retries' => 5,
            '--max-runtime' => 100,
            '--failed' => 'failed-send-newsletter',
            'queue' => 'send-newsletter',
        ));
    }
}
