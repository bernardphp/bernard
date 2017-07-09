<?php

namespace Bernard\Tests\Command;

use Bernard\Message\PlainMessage;
use Bernard\Command\ProduceCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ProduceCommandTest extends \PHPUnit\Framework\TestCase
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
        $message = new PlainMessage('SendNewsletter');

        $this->producer->expects($this->once())->method('produce')->with($this->equalTo($message));

        $tester = new CommandTester($command);
        $tester->execute(array(
            'name' => 'SendNewsletter'
        ));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidJsonThrowsException()
    {
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
        $message = new PlainMessage('SendNewsletter', array('foo' => 'bar'));

        $this->producer->expects($this->once())->method('produce')->with($this->equalTo($message));

        $tester = new CommandTester($command);
        $tester->execute(array(
            'name'    => 'SendNewsletter',
            'message' => '{"foo":"bar"}'
        ));
    }
}
