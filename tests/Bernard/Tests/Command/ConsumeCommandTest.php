<?php

namespace Bernard\Tests\Command;

use Bernard\Command\ConsumeCommand;
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

        $this->consumer->expects($this->once())->method('consume')->with($this->equalTo($queue), $this->equalTo(array(
            'max-runtime' => 100,
        )));

        $tester = new CommandTester($command);
        $tester->execute(array(
            '--max-runtime' => 100,
            'queue' => 'send-newsletter',
        ));
    }
}
