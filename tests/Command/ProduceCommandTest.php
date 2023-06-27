<?php

namespace Bernard\Tests\Command;

use Bernard\Message\PlainMessage;
use Bernard\Command\ProduceCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ProduceCommandTest extends \PHPUnit\Framework\TestCase
{
    protected $producer;

    public function setUp(): void
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
        $tester->execute([
            'name' => 'SendNewsletter',
        ]);
    }

    public function testInvalidJsonThrowsException()
    {
        $this->expectException(\RuntimeException::class);

        $command = new ProduceCommand($this->producer);

        $tester = new CommandTester($command);
        $tester->execute([
            'name' => 'SendNewsletter',
            'message' => '{@*^#"foo":"bar"}',
        ]);
    }

    public function testItProducesMessageWithData()
    {
        $command = new ProduceCommand($this->producer);
        $message = new PlainMessage('SendNewsletter', ['foo' => 'bar']);

        $this->producer->expects($this->once())->method('produce')->with($this->equalTo($message));

        $tester = new CommandTester($command);
        $tester->execute([
            'name' => 'SendNewsletter',
            'message' => '{"foo":"bar"}',
        ]);
    }
}
