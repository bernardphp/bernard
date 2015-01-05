<?php

namespace Bernard\Tests\Command;

use Bernard\Message\DefaultMessage;
use Bernard\Command\ProduceCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ProduceCommandTest extends \PHPUnit_Framework_TestCase
{
    protected $producer;

    public function setUp()
    {
        $this->producer = $this->getMockBuilder('Bernard\Producer')
            ->disableOriginalConstructor()->getMock();
    }

    public function testProduceMessageWithNoArguments()
    {
        $command = new ProduceCommand($this->producer);
        $message = new DefaultMessage('SendNewsletter');

        $this->producer->expects($this->once())->method('produce')->with($this->equalTo($message));

        $tester = new CommandTester($command);
        $tester->execute(array(
            'name' => 'SendNewsletter'
        ));
    }

    public function testInvalidJsonThrowsException()
    {
        $this->setExpectedException('RuntimeException');

        $command = new ProduceCommand($this->producer);

        $tester = new CommandTester($command);
        $tester->execute(array(
            'name'    => 'SendNewsletter',
            'message' => '{@*^#"foo":"bar"}'
        ));
    }

    public function testItProducesMessageWithData()
    {
        $command = new ProduceCommand($this->producer);
        $message = new DefaultMessage('SendNewsletter', array('foo' => 'bar'));

        $this->producer->expects($this->once())->method('produce')->with($this->equalTo($message));

        $tester = new CommandTester($command);
        $tester->execute(array(
            'name'    => 'SendNewsletter',
            'message' => '{"foo":"bar"}'
        ));
    }
}
