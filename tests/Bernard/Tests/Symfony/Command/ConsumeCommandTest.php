<?php

namespace Bernard\Tests\Symfony\Command;

use Bernard\Symfony\Command\ConsumeCommand;
use Symfony\Component\Console\Output\NullOutput;

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

        $this->queues->expects($this->once())->method('create')->with($this->equalTo('send-newsletter'))
            ->will($this->returnValue($queue));

        $consumer = $this->getMock('Bernard\ConsumerInterface');
        $consumer->expects($this->once())->method('consume')->with($this->equalTo($queue), $this->equalTo(array(
            'max_retries' => 5,
            'max_runtime' => null,
        )));

        $command = $this->getMockBuilder('Bernard\Command\ConsumeCommand')
            ->setMethods(array('getConsumer'))
            ->setConstructorArgs(array($this->services, $this->queues))->getMock();
        $command->expects($this->any())->method('getConsumer')->will($this->returnValue($consumer));

        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $input->expects($this->any())->method('getArgument')->with($this->equalTo('queue'))->will($this->returnValue('send-newsletter'));
        $input->expects($this->at(1))->method('getOption')->with($this->equalTo('max-retries'))->will($this->returnValue(5));
        $input->expects($this->at(2))->method('getOption')->with($this->equalTo('max-runtime'))->will($this->returnValue(null));

        $command->execute($input, new NullOutput());
    }
}
