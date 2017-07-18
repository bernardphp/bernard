<?php

namespace Bernard\Tests\Command;

use Bernard\Command\ConsumeCommand;
use Bernard\QueueFactory\InMemoryFactory;
use Symfony\Component\Console\Tester\CommandTester;

class ConsumeCommandTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->queues = new InMemoryFactory;
        $this->consumer = $this->getMockBuilder('Bernard\Consumer')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @medium
     */
    public function testItConsumes()
    {
        $command = new ConsumeCommand($this->consumer, $this->queues);
        $queue = $this->queues->create('send-newsletter');

        $this->consumer->expects($this->once())->method('consume')->with($this->identicalTo($queue), $this->equalTo(array(
            'max-runtime' => 100,
            'max-messages' => 10,
            'stop-when-empty' => true,
            'stop-on-error' => false,
        )));

        $tester = new CommandTester($command);
        $tester->execute(array(
            '--max-runtime' => 100,
            '--max-messages' => 10,
            '--stop-when-empty' => true,
            '--stop-on-error' => false,
            'queue' => 'send-newsletter',
        ));
    }

    public function testItConsumesRoundRobin()
    {
        $command = new ConsumeCommand($this->consumer, $this->queues);

        $args = array(
            'max-runtime' => 100,
            'max-messages' => 10,
            'stop-when-empty' => true,
            'stop-on-error' => false,
        );

        $this->consumer->expects($this->once())->method('consume')->with($this->isInstanceOf('Bernard\Queue\RoundRobinQueue'), $args);

        $tester = new CommandTester($command);
        $tester->execute(array(
            '--max-runtime' => 100,
            '--max-messages' => 10,
            '--stop-when-empty' => true,
            '--stop-on-error' => false,
            'queue' => ['queue-1', 'queue-2'],
        ));
    }
}
